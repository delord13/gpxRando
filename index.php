<?php
////////////////////////////////////////////////////////////////////////////////
//                            index.php
//
//                            application gpxRando
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////
/*
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/

	// contôle de paramétrage
//	$f = fopen('inc/config.sys.php','r');
	if (!file_exists('inc/config.inc.php')) {
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title></title>
  </head>
  <body style="   font-family: sans-serif;">
    <p> L'application gpxRando n'a pas encore été paramétrée.</p>
    <p>Veuillez :</p>
    <ol>
      <li>ouvrir le fichier <b>inc/config.inc.php_MODELE</b> dans
        un éditeur de texte</li>
      <li>modifier ce fichier en fonction de vos choix de paramétrage et y
        insérer vos clés (Géoportail, IBPIndex et éventuellement
        OpenRouteService)</li>
      <li>enregistrer ce fichier dans le répertoire inc en le renommant <b>config.inc.php</b></li>
    </ol>
    <p> Vous pourrez ensuite supprimer le fichier config.inc.php_MODELE </p>
  </body>
</html>

<?php
		die();
	}
	// fin contrôle de paramétrage
	
	session_start();
	output_add_rewrite_var('SESSION_NAME',session_name());

require 'inc/config.inc.php';
require 'inc/common.inc.php';

	// log
	enregistrerLog('gpxRando', 'entrée dans l\'application', '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type">
	<title>gpxRando</title>
		<style type="text/css">
		<!--
		body {font-family:sans-serif; font-size:small; text-align: justify;}
		h1 {color : #333399;  	line-height:50%;}
		h2 {color : #333399;  	line-height:50%;}
		h3 {color : #333399;  	line-height:50%;}
		h4 {color : #333399;  	line-height:50%;}
		hr {color : #333399;  	line-height:50%;}
		table  {  text-align: left; width: 100%; border-width:thin; border-color:blue; empty-cells:show;border-collapse:collapse; }
		td {border-width:thin; border-color:blue; empty-cells:show;border-collapse:collapse; font-family:sans-serif; font-size:small; }
		p {margin-top: 2px; margin-bottom: 2px;}
		ul {margin-top: 2px; margin-bottom: 2px;}

		-->
		</style>
		<link rel="shortcut icon" type="image/png" href="images/gpx2tdm_ico.png" />
</head>
<body>
	<div id="patience" style="position: absolute; top: 50%; right: 30%; z-index: 10001; width: 40%;  padding: 10px; background-color:#fffca2; border-width: 3px; border-color: #336699; border-style: solid; display: none; overflow: visible; font-family:sans-serif; font-size:medium; text-align: justify;">
		<p>
			Le temps d'attente pour l'ouverture du tableau de marche dépend du nombre de points de trace dont il faut obtenir l'altitude du Service Alticodage de l'IGN.
		</p>
		<p>
			Patience !
		</p>
		<p>
			Si au bout d'une dizaine de secondes vous n'avez pas obtenu le tableau de marche, c'est que le Service Alticodage de l'IGN ne répond pas ; il faudra ré-essayer plus tard.
		</p>
	</div>
	<div style="float: left;">
		<h1 style="float: left;">gpxRando : <span style="font-size: medium; font-style: italic;">de la trace gpx au tableau de marche</span>  <span style="font-size: small;" > v <?php echo($GLOBALS['numeroVersion']); ?> </span> </h1>
	</div>

<?php 
	if (URL_RETOUR!="") {

?>
	<div style="float: left;">
			<form action="../index.php">
				<input name="envoi" type="button" value="<?php echo RETOUR_TXT; ?>" onClick="window.location ='<?php echo URL_RETOUR; ?>'; return false;"
				style="margin-top: 15px; margin-left: 20px; width: 240px; text-align: center;">
			</form>
	</div>
<?php
	}
?>
<?php
	if (LOGO_URL!="") {
?>
	<div style="float: right;">
            <img alt="" src="<?php echo LOGO_URL; ?>" style="height: 50px;  width: auto;  float: right;"> 
	</div>

<?php
	}
?>

		<p style="clear: both;">
		gpxRando est un logiciel libre sous licence <a href="https://cecill.info/" target="_blank">CeCILL</a> disponible sur <a href="https://github.com/delord13/gpxRando" target="_blank">gitHub</a>. Des questions, des critiques, des suggestions ? Ecrivez à : gpx2tdm&#8249;arobase&#8250;free.fr <br>
		<span style="font-style: italic;"> Pour la consultation de ce site, il est conseillé d'utiliser le navigateur <a href="http://www.mozilla.org/fr/firefox/" target="_blank">Mozilla Firefox</a>.</span>
		</p>

		<hr style="clear: both;width: 100%; height: 2px;">
		
	<h2>Carte IGN API Géoportail</h2>
	<div style="margin-left: 3%; width: 75%;  float: left;">
		<form enctype="multipart/form-data" method="post"  target="_blank"
		action="carte.php" name="formCarte">
			<input type="hidden" name="origine" value="index" >
			<input type="hidden" name="modeVisualiser" value="portrait" >
			<p>Cette application permet d'afficher dans un nouvel onglet une carte IGN imprimable au format A4 grâce à l'<a href="http://api.ign.fr/accueil">API Géoportail</a>.
			</p>
			<p>
			Le carroyage UTM est de nouveau disponible pour la France Métropolitaine aux niveaux de zom correspondant aux cartes IGN 1/25000.
			</p>
		<fieldset>
			<legend>Afficher une carte imprimable A4
			</legend>
			<p style="display: none;"><input name="etiquette" value="etiquette" type="checkbox"> à cocher pour afficher les noms des éventuels points de passage</p>
			<p>fichier GPX (contenant une trace ou une route) à ajouter éventuellement : <input name="fichierGpx" type="file" onChange="document.getElementById('carteAuto').style.display='inline';">
			</p>
		</fieldset>
	</div>

	<div style="margin-left: 78%; width: 22%; text-align: center;">
		<p style="text-align: center; display: none;" id="carteAuto">
			<input style=" font-size: medium; width: 190px;" name="carteAuto" value="carte A4 Auto" title="carte au format portrait ou paysage selon la géométrie de la trace" onClick="ch = this.form.fichierGpx.value; if (!(/\.gpx$/i.test(ch)) && ch!='')  { alert('\''+ch+'\' n\'est pas un fichier gpx !'); this.form.fichierGpx.value='';} else  {this.form.modeVisualiser.value='auto'; this.form.submit();};" type="button">
		</p>
		
		<p style="text-align: center;">
			<input style=" font-size: medium; width: 190px;" name="cartePortrait" value="carte A4 Portrait"  title="carte au format portrait" onClick="ch = this.form.fichierGpx.value; if (!(/\.gpx$/i.test(ch)) && ch!='')  { alert('\''+ch+'\' n\'est pas un fichier gpx !'); this.form.fichierGpx.value='';} else  {this.form.modeVisualiser.value='portrait'; this.form.submit();};" type="button">
		</p>
		
		<p style="text-align: center;">
			<input style=" font-size: medium; width: 190px;" name="cartePaysage" value="carte A4 Paysage" title="carte au format paysage" onClick="ch = this.form.fichierGpx.value; if (!(/\.gpx$/i.test(ch)) && ch!='')  { alert('\''+ch+'\' n\'est pas un fichier gpx !'); this.form.fichierGpx.value='';} else  {this.form.modeVisualiser.value='paysage'; this.form.submit();};" type="button">
		</p>
	</div>	
		</form>
	<p style="clear: left;">&nbsp;</p>
	<hr style="width: 100%; height: 2px; clear: left;">
	
	<h2>gpxRandoEditeur</h2>

	<div style="margin-left: 3%; width: 75%;  float: left;">
		<p style="font-weight: bold;">
		La version 2 de l'API Géoportail sur laquelle gpxRandoEditeur était basée a cessé définitvement de fonctionner le 27 novembre 2019.
		</p>
		<p style="font-weight: bold;">
		En attendant que nous trouvions une solution pour passer gpxRandoEditeur en version 3 de l'API Géoportail en conservant ses fonctionnalités, nous vous conseillons d'utiliser <a href="https://www.visugpx.com/editgpx/" target="_blank">VisuGpx</a> pour créer et éditer vos traces GPX.
		</p>
	</div>
	<div style="margin-left: 78%; width: 22%; text-align: center;">
		<br>
      <form action="https://www.visugpx.com/editgpx/" method="get" target="_blank">
         <button style=" font-size: medium; width: 190px;" type="submit">VisuGpx</button>
      </form>

	</div>
	
	<p style="clear: left;">&nbsp;</p>
	<hr style="width: 100%; height: 2px; clear: left;">

	<h2>gpx2tdm</h2>
	<div style="margin-left: 3%; width: 75%;  float: left;">
		<p>Cette application permet de créer et/ou modifier un tableau de marche à partir d'un fichier gpx contenant une trace (trk) ou une route (rte) et des points de passage (wpt) décrivant une rando. Elle fournit aussi un profil altimétrique et permet de créer une fiche descriptive de la rando. Elle permet enfin d'imprimer la carte.</p>

			<h3>Création et édition d'un tableau de marche à partir d'un fichier gpx</h3>
		<form  enctype="multipart/form-data" method="post" action="gpx2tdm.php" name="formTDM">
		<fieldset>
			<legend>&nbsp;Ouvrir un ou des fichiers gpx contenant une trace ou une route et des points de passage&nbsp;</legend>
			<input type="hidden" name="mode" value="standard" >
			<input type="hidden" name="newAction" value="" >
			<table style="text-align: left; width: 100%;" border="0" cellpadding="2"
			cellspacing="2">
				<tbody>
					<tr>
						<td style="vertical-align: top;"><input name="recalculerAltitude" id="recalculerAltitude" value="recalculerAltitude" type="checkbox" <?php if(RECALCUL_ALTITUDES) echo('checked="checked"');  ?>> à cocher si vous souhaitez que les altitudes soient recalculées à l'aide du Service Alticodage de Géoportail basé sur le Modèle Numérique de Terrain RGE Alti au pas de 5m

                  </td>
					</tr>
					<tr>
						<td style="vertical-align: top;"><input name="ajouterDA" value="ajouterDA" type="checkbox" <?php if(AJOUT_DEPART_ARRIVEE) echo('checked="checked"');  ?>> à cocher si vous souhaitez que le point de départ et le point d'arrivée soient ajoutés si nécessaire
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top;">fichier gpx à ouvrir contenant la trace (trk) ou une route (rte) et, éventuellement, les points de passage (wpt) : 	<input name="fichierGpx" type="file" onChange="this.form.fichierTdm.value=''; ">
						</td>
						<td style="vertical-align: middle; text-align: left;"
						rowspan="2">
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top;">éventuellement en complément, fichier gpx à ouvrir contenant les points de passage (wpt) : <input name="fichierWpt" type="file" onChange="this.form.fichierTdm.value=''; ">
						</td>inline
					</tr>
				</tbody>
			</table>
		</fieldset>
		

		<h3>ou Chargement et édition d'un tableau de marche créé par gpx2tdm</h3>
		<fieldset>
			<legend>&nbsp;Ouvrir un fichier tdm créé avec gpx2tdm&nbsp;</legend>
			
			<table style="text-align: left; width: 100%;" border="0" cellpadding="2"
			cellspacing="2">
				<tbody>
					<tr>
						<td style="vertical-align: top;">fichier tdm à ouvrir&nbsp;:&nbsp;<input name="fichierTdm" type="file" onChange="this.form.fichierGpx.value=''; this.form.fichierWpt.value='';">
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	
	<div style="margin-left: 78%; width: 22%; text-align: center;">
	
			<br><br><br><br><br><br><br><br><br>
			<p style="text-align: center;">
			<input style=" font-size: medium; width: 190px;" name="validation" value="Ouvrir gpx2tdm" onClick="
				if (this.form.fichierGpx.value=='' && this.form.fichierTdm.value=='') alert('Veuillez indiquer le fichier à traiter.');
				else {
					if (this.form.fichierGpx.value!='') this.form.newAction.value='creer';
					else this.form.newAction.value='charger';
					ch = this.form.fichierGpx.value;
					if (ch!='' && !(/\.gpx$/i.test(ch))) { 
						var nom = ch.replace(/C:\\fakepath\\/,'');
						alert('\''+nom+'\' n\'est pas un fichier gpx !');
						this.form.fichierGpx.value = '';
					} 

					else {
						ch = this.form.fichierWpt.value;
						if (ch!='' && !(/\.gpx$/i.test(ch)))  { 
							var nom = ch.replace(/C:\\fakepath\\/,'');
							alert('\''+nom+'\' n\'est pas un fichier gpx !'); 
							this.form.fichierWpt.value = '';
						}
						else {
							ch = this.form.fichierTdm.value;
							if (ch!='' && !(/\.tdm$/i.test(ch))) { 
								var nom = ch.replace(/C:\\fakepath\\/,'');
								alert('\''+nom+'\' n\'est pas un fichier tdm !');  
								this.form.fichierTdm.value = '';}
							else {
								if (document.getElementById('recalculerAltitude').checked==true) {
									document.getElementById('patience').style.display = 'inline';
								} 
								body.style.cursor = 'wait';
								this.form.submit();
							}
						}
					}
				}
			" 
			type="button">
			</p>
		</form>
	</div>
	<p style="clear: left;">&nbsp;</p>
	

	<hr style="width: 100%; height: 2px; clear: left;">

	<?php
	// mentions légales
	if (MENTIONS_LEGALES!="") {

?>
	
	<p style="font-size: 8pt; font-style: italic;">
	<?php
		echo(MENTIONS_LEGALES);
	?>
	</p>
	
<?php
	}
?>

	

</body>
</html>