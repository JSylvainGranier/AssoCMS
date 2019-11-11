<?php
$searchedTerm = $ARGS ["searchPerson"];
$searchedTerm = strtolower ( $searchedTerm );

$sql = "select * from personne where
	(allowEmails = true
		or wantPaperRecap = true
	)
	and
	(
	lower(nom) like '%{$searchedTerm}%'
	or lower(prenom) like '%{$searchedTerm}%'
	or lower(email) like '%{$searchedTerm}%'
	)
	order by nom, prenom ";

$persDao = new Personne ();
$personnes = array ();

if (strlen ( $searchedTerm ) > 0) {
	$personnes = $persDao->getObjectListFromQuery ( $sql );
}

foreach ( $personnes as $aPersonne ) {
	/* @var $aPersonne Personne */
	$aPersonne->email = null;
	$aPersonne->telFixe = null;
	$aPersonne->telPortable = null;
	$aPersonne->adrL1 = null;
	$aPersonne->adrL2 = null;
	$aPersonne->adrL3 = null;
	$aPersonne->adrVille = null;
	$aPersonne->adrCP = null;
	$aPersonne->roles = null;
	$aPersonne->trombiFile = null;
	$aPersonne->passwordHash = null;
}

$page->setStandardOuputDisabled ( true );
if (isPhpUp ()) {
	echo json_encode ( $personnes );
} else {
	echo json_encode ( $personnes, JSON_UNESCAPED_UNICODE );
}

