<?php
class Evenement extends HasMetaData {
	public $idEvenement;
	public $organisateur1;
	public $organisateur2;
	public $page;
	public $dateDebut;
	public $dateFin;
	public $emplacement;
	public $annule = false;
	public function getPrimaryKey() {
		return $this->idEvenement;
	}
	public function setPrimaryKey($newId) {
		$this->idEvenement = $newId;
	}
	private static $memberDeclaration;
	static function getMembersDeclaration() {
		if (is_null ( Evenement::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idEvenement", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$page = new SqlColumnMappgin ( "fkPage", "Page décrivant l'évenement", SqlColumnTypes::$INTEGER );
			$page->setForeing ( new Page (), "page", true, true );
			
			$organis1 = new SqlColumnMappgin ( "fkPersonne1", "Organisateur 1 de l'évenement", SqlColumnTypes::$INTEGER );
			$organis1->setForeing ( new Personne (), "organisateur1", false, true );
			
			$organis2 = new SqlColumnMappgin ( "fkPersonne2", "Organisateur 2 de l'évenement", SqlColumnTypes::$INTEGER );
			$organis2->setForeing ( new Personne (), "organisateur2", false, true );
			
			Evenement::$memberDeclaration = array (
					$pk,
					$page,
					new SqlColumnMappgin ( "dateDebut", "Date de début", SqlColumnTypes::$DATETIME ),
					new SqlColumnMappgin ( "dateFin", "Date de fin", SqlColumnTypes::$DATETIME ),
					new SqlColumnMappgin ( "emplacement", "Emplacement de l'évenement", SqlColumnTypes::$VARCHAR, 255 ),
					new SqlColumnMappgin ( "annule", "L'évenement est annulé", SqlColumnTypes::$BOOLEAN ),
					$organis1,
					$organis2 
			);
			
			Evenement::$memberDeclaration = array_merge ( Evenement::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Evenement::$memberDeclaration;
	}
	public function getShortToString() {
		return "Evenement " . $this->formatDates ();
	}
	protected function getNaturalOrderColumn() {
		return "dateDebut";
	}
	public function getAllInCategorieByDate($catId, $fetchPastEvents = false) {
		$past = "";
		if (! $fetchPastEvents) {
			$past = "and dateDebut >= CURDATE( )";
		}
		$etat = Page::getSqlFilterForPagePublicationState ();
		$sql = "select evt.* from evenement evt join page p on p.idPage = evt.fkPage where p.fkCategorie = {$catId} {$past} and {$etat} order by dateDebut asc";
		return $this->getObjectListFromQuery ( $sql );
	}
	
	/**
	 * Retourne un Evenement en fonction de l'id de la Page à laquelle
	 * l'Evenement est lié.
	 *
	 * @param int $idPage        	
	 * @return Evenement
	 */
	public function findByPageId($idPage) {
		$sql = "select * from {$this->getTableName()} where fkPage = {$idPage}";
		return $this->getOneObjectOrNullFromQuery ( $sql );
	}
	
	/**
	 * Retourne tous les évenements qui n'ont pas encore commencé.
	 *
	 * @return Ambigous <multitype:, multitype:Persistant >
	 */
	public function getAllByDate() {
		$sql = "select evt.* from evenement evt where dateDebut >= CURDATE( ) order by dateDebut asc";
		return $this->getObjectListFromQuery ( $sql );
	}

	/**
	 * Retourne tous les évenements qui sont passés et qui sont pas trop vieux
	 *
	 * @return Ambigous <multitype:, multitype:Persistant >
	 */
	public function getAllInPast($nbMonth) {
		$sql = "select evt.* from evenement evt where dateDebut <= CURDATE( ) and dateDebut >= DATE_SUB(CURDATE(),INTERVAL {$nbMonth} MONTH) order by dateDebut desc";
		return $this->getObjectListFromQuery ( $sql );
	}

	public function getCountInFuture() {
		$sql = "select count(*) as 'count' from evenement evt where dateDebut >= CURDATE( )";
		$return = $this->getDataFromQuery ( $sql );
		return $return [0] ["count"];
	}
	
	public function getEvenementsForPhotoBook(){
		$sql = "SELECT COUNT( atch.idAttachment ), e . * 
				FROM page p
				JOIN evenement e ON e.fkPage = p.idPage
				JOIN attachment atch on p.idPage = atch.fkPage
				WHERE atch.typeMime LIKE  'image/%'


				GROUP BY (e.idEvenement)
				HAVING COUNT( atch.idAttachment ) >0

				ORDER BY e.dateDebut DESC ";
		return $this->getObjectListFromQuery ( $sql );
	}
	
	/**
	 * Retourne les evenements à venir et pour le mois courrant, éventuellement
	 * de la catégorie passée en paramètre.
	 *
	 * @param string $idCategorie        	
	 */
	public function getCurrentMonthEvents($idCategorie = null) {
		$catFilter = "";
		if ($idCategorie != null) {
			$catFilter = " and pg.fkCategorie = {$idCategorie} ";
		}
		
		//$etatFilter = Page::getSqlFilterForPagePublicationState ();
		$etatFilter = " 1 = 1";
		
		$myMaxDate = new MyDateTime ();
		$myMaxDate->date = mktime ( 23, 59, 59, date ( 'n' ), date ( "t" ), date ( 'Y' ) );
		
		$sql = "select evt.* from evenement evt join page pg on evt.fkPage = pg.idPage 
 				where evt.dateDebut > CURDATE() 
 				and evt.dateDebut < '{$myMaxDate->formatMySql()}' 
 				and {$etatFilter}
 				{$catFilter} 
 				order by evt.dateDebut";
		
		return $this->getObjectListFromQuery ( $sql );
	}
	public function formatDates($longDay = false, $displayArticle = true) {
		setlocale ( LC_TIME, "fr_FR" );
		$dateFormat = "%d/%m/%y";
		if ($longDay) {
			$dateFormat = "%A %d %B %Y";
		}
		$ret = "";
		if (is_null ( $this->dateDebut ) && is_null ( $this->dateFin )) {
			$ret .= "Aucune date";
		} else if (is_null ( $this->dateDebut ) && ! is_null ( $this->dateFin )) {
			$ret .= "Jusqu'au " . strtolower ( $this->dateFin->formatLocale ( $dateFormat ) );
			if ($this->dateFin->format ( "H:i" ) != "00:00") {
				$ret .= $this->dateFin->format ( " H\hi" );
			}
		} else if (! is_null ( $this->dateDebut ) && is_null ( $this->dateFin )) {
			$art = $displayArticle ? "A partir du " : "";
			$ret .= $art . strtolower ( $this->dateDebut->formatLocale ( $dateFormat ) );
			if ($this->dateDebut->format ( "H:i" ) != "00:00") {
				$ret .= $this->dateDebut->format ( " H\hi" );
			}
		} else if ($this->dateDebut->formatLocale ( $dateFormat ) == $this->dateFin->formatLocale ( $dateFormat )) {
			$art = $displayArticle ? "Le " : "";
			$ret .= $art . strtolower ( $this->dateDebut->formatLocale ( $dateFormat ) );
			
			if ($this->dateDebut->format ( "H:i" ) != "00:00") {
				if ($this->dateDebut->format ( "H:i" ) == $this->dateFin->format ( "H:i" )) {
					$ret .= " à " . $this->dateDebut->format ( "H\hi" );
				} else {
					if ($this->dateFin->format ( "H:i" ) != "00:00") {
						$ret .= " de " . $this->dateDebut->format ( "H\hi" ) . " à " . $this->dateFin->format ( "H\hi" );
					} else {
						$ret .= " à partir de " . $this->dateDebut->format ( "H\hi" );
					}
				}
			}
		} else {
			$ret .= "du " . strtolower ( $this->dateDebut->formatLocale ( $dateFormat ) );
			if ($this->dateDebut->format ( "H:i" ) != "00:00") {
				$ret .= $this->dateDebut->format ( " à H\hi" );
			}
			$ret .= " au " . strtolower ( $this->dateFin->formatLocale ( $dateFormat ) );
			if ($this->dateFin->format ( "H:i" ) != "00:00") {
				$ret .= $this->dateFin->format ( " à H\hi" );
			}
		}
		
		return ucfirst ( $ret );
	}
	
	/**
	 *
	 * @return MyDateTime
	 */
	public function getDateDebut() {
		return $this->dateDebut;
	}
	
	/**
	 *
	 * @return MyDateTime
	 */
	public function getDateFin() {
		return $this->dateFin;
	}
	
	/**
	 *
	 * @return Page
	 */
	public function getPage() {
		return $this->fetchLasyObject ( "page", "Page" );
	}
	
	/**
	 *
	 * @return Personne
	 */
	public function getOrganisateur1() {
		return $this->fetchLasyObject ( "organisateur1", "Personne" );
	}
	
	/**
	 *
	 * @return Personne
	 */
	public function getOrganisateur2() {
		return $this->fetchLasyObject ( "organisateur2", "Personne" );
	}
	
	/**
	 * Si la page à une description, une introduction plus longue que celle renvoyée sans HTML, ou des pièces jointes,
	 * retourne un lien "Lire la suite...", éventuellement accompagné d'une petite icone indiquant la présance de pièces jointes.
	 */
	public function getReadMoreLink() {
		$link = $this->getShowURL ();
		
		$filesIcons = $this->getPage ()->getAttachmentPreciInLink ();
		
		$suiteLenght = strlen($this->getPage()->suite);
		$iconsLenght = strlen($filesIcons);
		
		if ($suiteLenght > 15 || $iconsLenght > 3){
			$readMoreLink = "<span style='font-weight:bold;'><a href='{$link}' class='readMoreLink'>Cliquez ici</a> pour lire la suite. {$filesIcons}</span>";
		} else {
			$readMoreLink = "";
		}
		
		
		return $readMoreLink;
	}
	public function delete() {
		$this->getPage ()->delete ();
		parent::delete ();
	}
	
	public static function deleteForPersonne(Personne $pers){
		$sql = "update evenement set fkPersonne1 = null where fkPersonne1 = ".$pers->idPersonne;
		Evenement::ask($sql);
		
		$sql = "update evenement set fkPersonne2 = null where fkPersonne2 = ".$pers->idPersonne;
		Evenement::ask($sql);
	}
}