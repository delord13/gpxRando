<?php
////////////////////////////////////////////////////////////////////////////////
//                            ficheRandoHtml.php
//
//                            application gpx2tdm
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////////////////////
// Main : action selon POST
////////////////////////////////////////////////////////////////////////////////

// session et include
require '../inc/sessionsMultiplesSousAppli.inc.php';
//session_start();
require '../inc/config.inc.php';
require '../inc/gpx2tdm.inc.php';


		
		// début html
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">	
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="content-type">
		<title>Fiche-rando</title>
		<link rel="shortcut icon" type="image/ico" href="../images/gpx2tdm.ico" />
		<style type="text/css" media="screen,print">
		<!--
			body {font-family: sans-serif; font-size: 12px;}
			p {margin-top: 3px; margin-bottom: 0px; text-align: justify;}
			.titre {margin-top: 5px; margin-bottom: 5px;font-size: 20px; font-weight: bold; text-align: center;}
			.sousTitre {font-size: 16px;  margin-bottom: 5px;font-weight: bold; text-align: center;}
			.presentation {font-size: 12px; margin-bottom: 5px; font-style: italic;}
			.remarques {font-size: 12px; margin-bottom: 5px; font-weight: bold; border: 1px solid black;}
			.gras {font-weight: bold;}
			.complements {font-style: oblique;}
		-->
		</style>
		</head>
		<body>

			<?php
			// traitement du logo
				if (!$_SESSION['ficheLogoAucun']) { 
			?>
			<div><img src="data:image/<?php echo $_SESSION['ficheLogoImageType']; ?>;base64,<?php echo $_SESSION['ficheLogoImage']; ?>"/>
			</div>		
			<?php 
				} 
			?>
			<br>
			<?php
			// traitement des titre sous-titre et date
				echo "<p class='titre'>".$_SESSION['ficheTitreFiche']."</p>";
				if ($_SESSION['ficheSousTitre']!="") echo "<p class='sousTitre'>".$_SESSION['ficheSousTitre']."</p>";
				$date = $_SESSION['date'];
				$tab_date = explode("/",$date);
				$jour = $tab_date[0];
				$mois = $tab_date[1];
				$an = $tab_date[2];
				setlocale(LC_TIME, 'fr_FR.UTF8');
				$laDate = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
				echo "<div class='sousTitre'>".$laDate."</div>";
				
				$presentation = "<p>".str_replace("\n","</p><p>",$_SESSION['fichePresentation'])."</p>";
				if ($presentation!="") echo "<div class='presentation'>".$presentation."</div>";
				
				$remarques = "<p>".str_replace("\n","</p><p>",$_SESSION['ficheRemarques'])."</p>";
				if ($_SESSION['ficheRemarques']!="") echo "<div class='remarques'>".$remarques."</div>";
				?>
			<div>
			<?php
			// traitement des rubriques
				//theme pour CPC
				if ($GLOBALS['clients']=="CPC") {
					if ($_SESSION['ficheThemeCode']!="") {
						// recherche intitulé du thème 
						$sql = "SELECT `intituleTheme` FROM `theme` WHERE `codeTheme`='{$_SESSION['ficheThemeCode']}' ";
						$res = mysqli_query($GLOBALS['lkId'],$sql);
						$unTheme = mysqli_fetch_assoc($res);

						echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['themeCode']['id']."</span> : ".$unTheme['intituleTheme']);
						if ($_SESSION['ficheThemeDescription']!="") {
							echo(" : ".$_SESSION['ficheThemeDescription']);
						}
						echo ("</p>");
					}
				}
				if ($_SESSION['ficheNiveau']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['niveau']['id']."</span> : ".$_SESSION['ficheNiveau']."</p>");

				if ($_SESSION['ficheIbpIndex']!="") {
					echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['ibpIndex']['id']."</span> : ".$_SESSION['ficheIbpIndex']);
					if ($GLOBALS['clients']=="PUBLIC" ) {
						echo("<br> Cotation \"Effort\" FFRandonnée selon indice IBP : 0-25 : Facile ; 26-50 : Assez facile ; 51-75 : Peu difficile ; 76-100 : Assez difficile ; >100 : Difficile</p>");
					}
					else {
						echo("</p>");
					}
				}
				if ($_SESSION['ficheDenivPosFiche']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['denivPosFiche']['id']."</span> : +".$_SESSION['ficheDenivPosFiche']."m</p>");
				if ($_SESSION['ficheDenivNegFiche']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['denivNegFiche']['id']."</span> : -".$_SESSION['ficheDenivNegFiche']."m</p>");
				if ($_SESSION['ficheLongueurFiche']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['longueurFiche']['id']."</span> : ".$_SESSION['ficheLongueurFiche']."km</p>");

				if ($_SESSION['ficheDureeFiche']!="") { 
					$h = floor($_SESSION['ficheDureeFiche']/60);
					$m = $_SESSION['ficheDureeFiche']%60;
					if ($m<10) $m = "0" . $m;
					echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['dureeFiche']['id']."</span> : ".$h."h".$m."</p>");

				$difficultes = str_replace("\n","</p><p>",$_SESSION['ficheDifficultes']);}
				if ($difficultes!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['difficultes']['id']."</span> : ".$difficultes."</p>");
				if ($_SESSION['ficheCarte']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['carte']['id']."</span> : ".$_SESSION['ficheCarte']."</p>");
				$rdv = str_replace("\n","</p><p>",$_SESSION['ficheRDV']);
				if ($rdv!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['rdv']['id']."</span> : ".$rdv."</p>");
				if ($_SESSION['ficheDepart']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['depart']['id']."</span> : ".$_SESSION['ficheDepart']."</p>");
				if ($_SESSION['ficheParking']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['parking']['id']."</span> : ".$_SESSION['ficheParking']."</p>");

				if ($_SESSION['ficheTrajetKm']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['trajetKm']['id']."</span> : ".$_SESSION['ficheTrajetKm']."</p>");				
				
            if ($GLOBALS['clients']!="CPC") {
					$trajet = str_replace("\n","</p><p>",$_SESSION['ficheTrajet']);
					if ($trajet!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['trajet']['id']."</span> : ".$trajet."</p>");	
				}
				
				if ($_SESSION['ficheCovoiturage']!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['covoiturage']['id']."</span> : ".$_SESSION['ficheCovoiturage']."</p>");
				$laRandonnee = str_replace("\n","</p><p>",$_SESSION['ficheLaRandonnee']);
				
				
				
				if ($laRandonnee!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['laRandonnee']['id']."</span> : </p><p>".$laRandonnee."</p>");
				$equipement = str_replace("\n","</p><p>",$_SESSION['ficheEquipement']);
				if ($equipement!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['equipement']['id']."</span> : ".$equipement."</p>");
				
				$animateur = str_replace("\n","</p><p>",$_SESSION['ficheAnimateur']);
				
				if ($animateur!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['animateur']['id']."</span> : ".$animateur."</p>");
				
				$ficheComplementsTitre = str_replace("\n","</p><p>",$_SESSION['ficheComplementsTitre']);
				if ($ficheComplementsTitre!="") $ficheComplementsTitre .= " : "; 
				
				$ficheComplements = str_replace("\n","</p><p>",$_SESSION['ficheComplements']);
                
                if ($ficheComplements!="") echo("<span class='complements'> <p><span class='gras'>".$ficheComplementsTitre."</span></p><p>".$ficheComplements."</p></span>");

            if ($GLOBALS['clients']=="CPC") {
					$trajet = str_replace("\n","</p><p>",$_SESSION['ficheTrajet']);
					if ($trajet!="") echo("<p><span class='gras'>".$GLOBALS['txt']['ficheRando']['trajet']['id']."</span> : ".$trajet."</p>");	
				}
			?>
			</div>
<?php
?>
	</body>
</html>
