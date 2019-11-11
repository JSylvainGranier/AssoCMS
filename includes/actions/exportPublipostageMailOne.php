<?php
/* @var $desti Personne */
$desti = $aDesti->getDestinataire ();
?>
<page backtop='0mm' backbottom='8mm'> <page_header>
<style type="text/css">
.marquerPliage {
	border-top: solid 1px #E5E4E3;
	position: absolute;
	top: 90mm;
	left: -10mm;
	width: 15mm;
}
</style>
<div class="marquerPliage">&nbsp;</div>
</page_header> <page_footer>
<p style="text-align: center; margin: 0mm; padding: 0mm;">[[page_cu]]/[[page_nb]]</p>
</page_footer>
<style type="text/css">
.adresse {
	width: 83mm;
	height: 32mm;
	border: solid 1px #E5E4E3;
	background-color: white;
	padding: 4mm;
	padding-left: 8mm;
	position: absolute;
	top: 28mm;
	left: 85mm;
}

.adresse p {
	margin: 0mm;
	padding: 0mm;
	padding-top: 3mm;
	font-size: 12pt;
}

.objet {
	font-weight: bold;
	background-color: #A8A7A7;
	text-align: center;
	overflow: hidden;
	vertical-align: middle;
}

p {
	margin: 0mm;
	padding: 0mm;
	padding-bottom: 1mm;
	padding-top: 1mm;
}

h1, h3, h4, h5 {
	margin: 0mm;
	padding: 0mm;
	margin-top: 4mm;
}
</style>

<table>
	<tr>
		<td><img src="ressources/template/logo.png" style="width: 27mm;" /></td>
		<td style="width: 55mm">
					<?php echo $headerContent; ?>
				</td>
		<td style="width: 94mm" class="objet">
					<?php echo $publipostage->objet; ?>
				</td>
	</tr>
</table>

<div class="adresse">
		<?php if(!is_null($desti)) { ?>
			<p>
		<b><?php echo $desti->getCiviliteNomPrenom(); ?></b>
	</p>
	<p><?php echo $desti->adrL1; ?></p>
	<p><?php echo $desti->adrL2; ?></p>
	<p><?php echo $desti->adrL3; ?></p>
	<p><?php echo $desti->adrCP." ".$desti->adrVille; ?></p>
		<?php } ?>
	
	</div>
	
	 
	<?php echo $message; ?>
</page>








