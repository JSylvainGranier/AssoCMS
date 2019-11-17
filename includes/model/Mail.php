<?php
class Mail extends HasMetaData {
	public $idMail;
	public $expediteur;
	public $destinataire;
	public $objet;
	public $message;
	public $sent = false;
	public $nbTentatives = 0;
	public function getPrimaryKey() {
		return $this->idMail;
	}
	public function setPrimaryKey($newId) {
		$this->idMail = $newId;
	}
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Mail::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idMail", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			Mail::$memberDeclaration = array (
					$pk,
					new SqlColumnMappgin ( "sent", "Message envoyé", SqlColumnTypes::$BOOLEAN ),
					new SqlColumnMappgin ( "nbTentatives", "Tentatives d'envoie", SqlColumnTypes::$INTEGER ),
					new SqlColumnMappgin ( "expediteur", "Expéditeur", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "destinataire", "Destinataire", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "objet", "Objet du message", SqlColumnTypes::$LONGTEXT ),
					new SqlColumnMappgin ( "message", "Corps du message", SqlColumnTypes::$LONGTEXT ) 
			);
			
			Mail::$memberDeclaration = array_merge ( Mail::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Mail::$memberDeclaration;
	}
	public function getShortToString() {
		$sent = $this->sent ? "OK" : "S" . $this->nbTentatives;
		return $sent . " @ " . $this->destinataire . " : " . $this->objet;
	}
	protected function getNaturalOrderColumn() {
		return "idMail";
	}
	
	/**
	 * Retourne une liste de Mail à traiter,
	 * la taille de la liste ne dépassant par $spoolSize
	 *
	 * @param int $spoolSize        	
	 */
	public function getNextSpoolContent($spoolSize, $maxTentatives) {
		$sql = "select * from mail where sent = false and nbTentatives < {$maxTentatives} order by lastUpdateOn asc limit {$spoolSize}";
		return $this->getObjectListFromQuery ( $sql );
	}
	
	/**
	 * Provoque l'envoie du message sur le champ.
	 * Ajoute une nouvelle tentative, et en fin d'envoie, enregistre les modifications.
	 */
	public function send() {
		$this->nbTentatives ++;
		$resultat = false;
		
		try {
			$email = new SmartPage ( "emailBody.html" );
			
			// $message = nl2br($this->message);
			$message = $this->message;
			
			$email->appendBody ( $message );
			
			$email->append ( "style", file_get_contents ( "ressources/template/style.css" ) );
			
			$email->append ( "style", file_get_contents ( "ressources/template/emailStyle.css" ) );
			
			$html = $email->buildPage ( false );
			
			$boundary = uniqid ( 'np' );
			
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "From: " . MAIL_FORM . "\r\n";
			
			if (! is_null ( $this->expediteur )) {
				$headers .= 'Reply-To: ' . $this->expediteur . "\r\n";
			}
			
			$to = $this->destinataire;
			if (defined ( "MAIL_REDIRECTION_TO" )) {
				$to = MAIL_REDIRECTION_TO;
			}
			$headers .= "To: " . $to . "\r\n";
			
			$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
			
			$emailCorpus = "This is a MIME encoded message.";
			$emailCorpus .= "\r\n\r\n--" . $boundary . "\r\n";
			$emailCorpus .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
			
			$emailCorpus .= "Hello,\nThis is a text email, the text/plain version. \n\nRegards,\nYour Name";
			
			$emailCorpus .= "\r\n\r\n--" . $boundary . "\r\n";
			$emailCorpus .= "Content-type: text/html;charset=utf-8\r\n\r\n";
			
			$emailCorpus .= $html;
			$emailCorpus .= "\r\n\r\n--" . $boundary . "--";
			
			$start_time = time ();
			
			$resultat = mail( $to, $this->objet, $emailCorpus, $headers );
			
			$time = time () - $start_time;
			$resultat = $resultat & ($time >= 2);
		} catch ( Exception $e ) {
		}
		
		$this->sent = $resultat;
		$this->save ();
	}
	/**
	 * Pour toutes les pages qui sont en état Proposition, fait un mail indiquand qu'il faut l'accepter.
	 */
	public function sendPropositionAlert() {
		$sql = "select * from page where etat = 10 and lastUpdateOn >= (select pValue from param where pKey='PROPOSITION_ALERT_LAST_EXEC')";
		
		$p = new Page ();
		$list = $p->getObjectListFromQuery ( $sql );
		
		if (count ( $list ) > 0) {
			$msg = "<p>Il existe des propositions de contenu sur le site : </p><ul>";
			
			foreach ( $list as $aPage ) {
				/* @var $aPage Page */
				$titre = $aPage->titre;
				$section = $aPage->getCategorieClassement ()->nom;
				$auteur = $aPage->getAuteur ()->getNomPrenom ();
				
				if ($aPage->isSubClass) {
					$evt = new Evenement ();
					$evt = $evt->findByPageId ( $aPage->getPrimaryKey () );
					$lien = "index.php?show&class=Evenement&id=" . $evt->getPrimaryKey ();
				} else {
					$lien = "index.php?show&class=Page&id=" . $aPage->getPrimaryKey ();
				}
				
				$msg .= "<li><a href='{$lien}'>{$titre}</a> dans la section {$section} par {$auteur}</li>";
			}
			
			$msg .= "</ul>";
			
			$mail = new Mail ();
			$mail->destinataire = EMAIL_ON_ERROR;
			$mail->message = $msg;
			$mail->expediteur = MAIL_FORM;
			$mail->objet = "Contenu en attente de validation sur le site";
			$mail->save ();
		}
		
		$p = new Param ();
		;
		$p = $p->findById ( PKeys::$PROPOSITION_ALERT_LAST_EXEC->key );
		$newDate = new MyDateTime ();
		$p->pValue = $newDate->format ( 'Y-m-d H:i:s' );
		$p->save ();
	}
	public function cleanAll() {
		$sql = "delete from mail where 1=1";
		$this->ask ( $sql );
	}
	public function cleanSent() {
		$sql = "delete from mail where sent = true";
		$this->ask ( $sql );
	}
	public function cleanError() {
		$MAX_TENTATIVES = Param::getValue ( PKeys::$MAIL_MAX_TENTATIVES, 3 );
		
		$sql = "delete from mail where sent = false and nbTentatives >= {$MAX_TENTATIVES}";
		$this->ask ( $sql );
	}
}

?>