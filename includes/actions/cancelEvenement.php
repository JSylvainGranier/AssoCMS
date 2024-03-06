<?php
$showForm = true;
$evenement = new Evenement ( $ARGS ["id"] );

if($ARGS["target"] == "restaure"){
	$evenement->annule = false;
	$evenement->save ();
	
	$page->appendNotification ( "Le rendez-vous n'est plus annulé." );
	
	$ACTIONS [] = array (
			"show",
			"class" => "Evenement",
			"id" => $ARGS ["id"]
	);
	
} else {
	if (array_key_exists ( "confirm", $ARGS )) {
		if ($ARGS ["confirm"] == "Annuler le rendez-vous") {
	
			$evenement->annule = true;
			$evenement->save ();
	
			if (array_key_exists ( "doPublipostage", $ARGS )) {
				$publipostage = new Publipostage ();
					
				$msg = "<p>Bonjour, </p>";
				$msg .= "<p>Le rendez-vous suivant est annulé : </p>";
				$msg .= "<blockquote><h4>{$evenement->getPage()->titre} ({$evenement->formatDates()})</h4><p>Section : {$evenement->getPage()->getCategorieClassement()->nom} </p><p>{$evenement->getPage()->introduction}</p></blockquote>";
					
				$publipostage->message = $msg;
				$publipostage->objet = "Annulation d'un rendez-vous.";
				$publipostage->save ();
					
				$idPubli = $publipostage->getPrimaryKey ();
					
				$page->appendNotification ( "Le rendez-vous est annulé.<br />Vous pouvez maintenant modifier le message puis l'envoyer aux membres de l'association." );
					
				$ACTIONS [] = array (
						"edit",
						"class" => "Publipostage",
						"id" => $idPubli
				);
			} else {
					
				$page->appendNotification ( "Le rendez-vous est annulé." );
					
				$ACTIONS [] = array (
						"show",
						"class" => "Evenement",
						"id" => $ARGS ["id"]
				);
			}
	
			$showForm = false;
		} else {
			$showForm = false;
			$ACTIONS [] = array (
					"show",
					"class" => "Evenement",
					"id" => $ARGS ["id"]
			);
		}
	}
	
	if ($showForm) {
	
		?>
	<article>
		<h1>Annulation d'un rendez-vous</h1>
		Voulez-vous vraiment annuler le rendez-vous suivant ?
		<blockquote>
			<h4><?php echo $evenement->getPage()->titre." (".$evenement->formatDates() ?>)</h4>
			<p>Section : <?php echo $evenement->getPage()->getCategorieClassement()->nom; ?> </p>
			<p><?php echo $evenement->getPage()->introduction ?></p>
		</blockquote>
		<form action="index.php?cancelEvenement&id=<?php echo $ARGS["id"]; ?>"
			method="post">
			<input type="checkbox" name="doPublipostage" id="doPublipostage" /><label
				for="doPublipostage">Créer un publipostage pour avertir les membres
				de l'association.</label>
	
			<p>
				<input type="submit" name="confirm" value="Revenir" /> <input
					type="submit" name="confirm" value="Annuler le rendez-vous" />
			</p>
		</form>
	
	</article>
	
	
	
	
	<?php
	}
}



?>


