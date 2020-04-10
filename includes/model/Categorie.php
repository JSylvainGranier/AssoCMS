<?php
class Categorie extends HasMetaData {
	public $idCategorie;
	public $nom;
	public $couleurRuban;
	public $couleurRubanSurvol;
	public $backgroundImage;
	public $ordre;
	public $textePresentation;
	public $pageList;
	public $iconClass;
	public $autoAffiliate = false;
	
	/* @var $personneGestionnaire Personne */
	public $personneGestionnaire;
	public $personnesAffilies;
	private static $memberDeclaration = null;
	public function getPrimaryKey() {
		return $this->idCategorie;
	}
	public function setPrimaryKey($newId) {
		$this->idCategorie = $newId;
	}
	static function getMembersDeclaration() {
		if (is_null ( Categorie::$memberDeclaration )) {
			$pk = new SqlColumnMappgin ( "idCategorie", null, SqlColumnTypes::$INTEGER );
			$pk->setPrimaryKey ( true );
			
			$nom = new SqlColumnMappgin ( "nom", "Nom catégorie", SqlColumnTypes::$VARCHAR, 50 );
			$iconClass = new SqlColumnMappgin ( "iconClass", "Icone de la catégorie", SqlColumnTypes::$VARCHAR, 50 );
			$nom->isNullable = false;
			$ordre = new SqlColumnMappgin ( "ordre", "Ordre d'affichage", SqlColumnTypes::$INTEGER );
			$cRuban = new SqlColumnMappgin ( "couleurRuban", "Couleur normale du ruban", SqlColumnTypes::$VARCHAR, 15 );
			$cRubanHover = new SqlColumnMappgin ( "couleurRubanSurvol", "Couleur du ruban au survol", SqlColumnTypes::$VARCHAR, 15 );
			$bkgImg = new SqlColumnMappgin ( "backgroundImage", "Image de fond pour les pages de cette catégorie", SqlColumnTypes::$VARCHAR, 255 );
			
			$gestionnaire = new SqlColumnMappgin ( "fkGestionnaire", "Gestionnaire de la catégorie", SqlColumnTypes::$INTEGER );
			$gestionnaire->setForeing ( new Personne (), "personneGestionnaire", false, true );
			
			$textePresentation = new SqlColumnMappgin ( "textePresentation", "Petit texte de présentation de la catégorie", SqlColumnTypes::$LONGTEXT );
			
			$autoAffiliate = new SqlColumnMappgin ( "autoAffiliate", "Tout le monde est automatiquement affilié à cette catégorie", SqlColumnTypes::$BOOLEAN );
			
			Categorie::$memberDeclaration = array (
					$pk,
					$nom,
					$textePresentation,
					$ordre,
					$cRuban,
					$cRubanHover,
					$bkgImg,
					$autoAffiliate,
					$gestionnaire,
			         $iconClass
			);
			
			Categorie::$memberDeclaration = array_merge ( Categorie::$memberDeclaration, HasMetaData::getMembersDeclaration () );
		}
		
		return Categorie::$memberDeclaration;
	}
	
	/**
	 * Retourne la liste des Pages qui sont directement liées à cette catégorie, et qui ne sont pas des Evenemnts.
	 */
	public function getPagesEnfants() {
		$sql = "select * from page where fkCategorie = {$this->idCategorie} and isSubClass = false";
		$p = new Page ();
		return $p->getObjectListFromQuery ( $sql );
	}
	
	/**
	 * Retourne le nombre d'evenement à venir dans la catégorie
	 */
	public function getNombreEvenementFutur() {
		$sql = "select count(*) as nbe from evenement evt join page p on p.idPage = evt.fkPage where p.fkCategorie = {$this->idCategorie} and dateDebut >= CURDATE( )";
		$result = $this->getDataFromQuery ( $sql );
		return $result [0] ["nbe"];
	}
	
	/**
	 * Retourne le nombre d'evenement lié à la catégorie
	 */
	public function getNombreEvenement() {
		$sql = "select count(*) as nbe from evenement evt join page p on p.idPage = evt.fkPage where p.fkCategorie = {$this->idCategorie} ";
		$result = $this->getDataFromQuery ( $sql );
		return $result [0] ["nbe"];
	}
	public function getShortToString() {
		return $this->nom;
	}
	protected function getNaturalOrderColumn() {
		return "ordre";
	}
	
	/**
	 *
	 * @return Personne
	 */
	public function getPersonneGestionnaire() {
		return $this->fetchLasyObject ( "personneGestionnaire", "Personne" );
	}
	public function getPageList() {
		return $this->fetchLasyCollection ( "pageList", "Page", "categorieClassement" );
	}
	
	/**
	 * Retourne toutes les catégories pour lesquelles les membres personnes sont automatiquement affiliées.
	 *
	 * @return array
	 */
	public function getAutoAffiliateCategories() {
		$sql = "select * from {$this->getTableName()} where autoAffiliate = 1";
		$list = $this->getObjectListFromQuery ( $sql );
		return $list;
	}
	
	/**
	 * Retourne une liste de toutes les Personnes qui sont affiliées à la catégorie, par affectation ou
	 * par le fait que cette catégorie est à affectation automatique.
	 */
	public function getAffiliatedPersonnes() {
		$persDao = new Personne ();
		if ($this->autoAffiliate) {
			return $persDao->getAll ();
		} else {
			
			$affectList = $this->getAffiliationCategorieList ();
			
			$persList = array ();
			
			/* @var $affilCateg AffiliationCategorie */
			foreach ( $affectList as $affilCateg ) {
				$affilCateg->getPersonne ();
				$persList [] = $affilCateg->personne;
			}
			
			return $persList;
		}
	}
	
	/**
	 * Retourne une liste des AffiliationCategorie concernant cette catégorie.
	 */
	public function getAffiliationCategorieList() {
		return $this->fetchLasyCollection ( "personnesAffilies", "AffiliationCategorie", "categorie" );
	}
	
	public static function deleteForPersonne(Personne $pers){
		$sql = "update categorie set fkGestionnaire = null where fkGestionnaire = ".$pers->idPersonne;
		Categorie::ask($sql);
	}
}