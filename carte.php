<?php
////////////////////////////////////////////////////////////////////////////////
//                            carte.php
//
//                            application gpxRando
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////
// reçoit par POST une chaîne xml = contenu du trk et des wpt ou un fichier gpx par $_FILES
// affiche la trace et les wpt sur cart IGN en utilisant l'API Geoportail
// utilise le répertoire temp où Apache peut lire et écrire
/*
	version leaflet de base mais ajout de GpPluginLeaflet pour le gestionnaire de couche et les coordonnées du curseur avec altitude
	js et css leaflet denière version
	=> map : type leaflet + GP layer switcher + GP Mouse position (altitude)
	création de la carte
		ajout des couches IGN
		ajout de la couche OSM topo
		génération des couches UTM (canvas)
		ajout des couches UTM
		génération de la couche numéro de carte IGN (utilisation du plugin KML ?)
		ajout de la couche numéro de carte IGN
		génération de la couche trk
		ajout de la couche trk (polyline)
		génération de la couche wpt : layers group ok markers sans noms des pdp
		génération de la couche wpt : layers group ok markers avec tooltips : noms des pdp
		ajout de la couche wpt avec nom
		génération de la couche symboles : drapeaux; point km ; nb de km
		ajout de la couche
		ajout de la couche tableau d'assemblage des cartes IGN (KML)
	
	ajout layers switcher geortail  après l'ajout de couches GP et de la couche trk mais avant les autres pour éviter les couches parasites (NB la couche trk génère une couche parasite qu'il faut supprimer du layer switcher)
	avec gestion de la transparence des couches (au moins de la couche photos aériennes) (Leaflet.Control.Appearance ?)$_POST['newAction']
	ordre des couches depuis le bas
		IGN et OSM topo
		photos aériennes
		UTMecho("GET : ");var_dump($_GET);
		limites administratives
		numéros de carte IGN
		trk
		wpt
		symboles
*/


/*
INUTILE $_SESSION n'est pas utilisé dans le script
//session_start();

echo("POST : ");var_dump($_POST);
die();
*/	

//	error_reporting(E_ALL);
//	ini_set("display_errors", 1);

	require 'inc/config.inc.php';
	require 'inc/common.inc.php';
	
	///////////////////////////////////////////////////////////////////////////////
	// titre de la page et donc du fichier image capturée
	$titre = titreDeLaPage();
	///////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////
	// nettoyer le répertoire /tmp des fichiers vieux de 15 minutes et plus
   nettoyerTmp();
 	///////////////////////////////////////////////////////////////////////////////
  
	///////////////////////////////////////////////////////////////////////////////
	// log
	enregistrerLog ('visualiserGpx', 'entrée dans visualiserGpx', '');
	///////////////////////////////////////////////////////////////////////////////
	
	
	///////////////////////////////////////////////////////////////////////////////
	// préparation de la carte selon fichier gpx fourni ou non
	///////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////
//	var_dump($_POST); var_dump($_GET); die();
	if (isset($_POST['origine'])) {
		if (isset($_POST['xml']) || (isset($_FILES['fichierGpx']['name']) && $_FILES['fichierGpx']['name']!="" )) {
			
		
			// trace couche GPX
			preparerCarteTrace();
		}
		else {
			preparerCarteSansFichierGpx();
		}
	} // fin si POST
	else {
		if (isset($_GET['fichierGpx'])) { // appel par spip IRLPT INUTILE !!!!
			preparerCarteTrace();
		}
		else {
			preparerCarteSansFichierGpx();
			
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////
	
	///////////////////////////////////////////////////////////////////////////////
   function preparerCarteTrace() {
	///////////////////////////////////////////////////////////////////////////////
      global $gpx, $trk, $nomCouche, $fichierGpxCheminWeb, $centreLat, $centreLon, $zoom, $formatAuto, $trkJs, $wptJs;
		if (isset($_POST['xml'])) { // pour appel hors gpxRando (bdTdm, spip...)
			// trace à afficher origine = gpx2tdm ou index
				$chaineGpx = stripslashes($_POST['xml']);
				if (isset($_POST['nomCouche'])) $nomCouche = $_POST['nomCouche'];
				else $nomCouche = "trace";
				// log
				enregistrerLog ('externe', 'affichage de la trace sur la carte IGN',$nomCouche.'.gpx');
		}
			
		else {
			if ($_FILES['fichierGpx']['name']!="") { // pour appel par index
				$fichierGpx = $_FILES['fichierGpx']['tmp_name'];
				$fp = fopen($fichierGpx, "rb");
				clearstatcache();
				$chaineGpx = fread($fp, filesize($fichierGpx));
				fclose($fp);
				$nomCouche = $_FILES['fichierGpx']['name'];
				$pos = strpos($nomCouche, ".gpx");
				$nomCouche = substr($nomCouche,0,$pos);
				// log
				enregistrerLog ('gpxRando', 'affichage de la trace sur la carte IGN',$_FILES['fichierGpx']['name']);
			}
			else die("C'est pas bon ça !");
		}
			


		// correction XML v1.1: remplace xml version="1.1" par xml version="1.0"
		$chaineGpx = str_replace('xml version="1.1"', 'xml version="1.0"', $chaineGpx);
		$chaineGpx = str_replace("xml version='1.1'", "xml version='1.0'", $chaineGpx);
		
		// conversion éventuelle de la route en trace	
		$chaineGpx = rte2trk($chaineGpx); // dans init.inc.php
			
		// enregistrer la chaine $chaineGpx un fichier gpx dans /tmp
		$nomFichierTemp = mt_rand(0,1000000);
		$fichierGpxCheminComplet = realpath(".")."/tmp/".$nomFichierTemp.".gpx";
		// création du fichier sur le serveur
		$leFichier = fopen($fichierGpxCheminComplet, "x+");
		fwrite($leFichier,$chaineGpx);
		fclose($leFichier);
		// on vérifie que le fichier a bien été créé
		$t_infoCreation['fichierCreer'] = false;
		if(file_exists($fichierGpxCheminComplet)!=true){
			die("on ne peut pas créer le fichier gpx: ".$fichierGpxCheminComplet);
		}
		// on applique les permission au fichier créé
		$retour = chmod($fichierGpxCheminComplet,intval("0777",8));
		// préparation de la création de la couche GPX
		$repSelf = $_SERVER['PHP_SELF'];
		$pos = strpos($repSelf, "carte.php");
		$repSelf = substr($repSelf,0,$pos);
		$fichierGpxCheminWeb = $repSelf."tmp/".$nomFichierTemp.".gpx";

		$gpx = simplexml_load_string($chaineGpx);
		
		// $trk: tableau des lat lon
		// trkjs: définition de la trace comme polyline pour Leaflet
		// s'il y a plusieurs trkseg, on les raccorde en 1 seule trace
		$trkJs = "[";
		$i = 0;
		foreach ($gpx->trk->trkseg as $unTrkseg) {
			foreach($unTrkseg->trkpt as $trkpt) {
				$trk[$i]['lat'] = (float) $trkpt['lat'];
				$trk[$i]['latDecalee'] = $trk[$i]['lat']+0.000135;
				$trk[$i]['lon'] = (float) $trkpt['lon'];
				if ($i!=0) $trkJs .= ',';
				$trkJs .= "[{$trkpt['lat']},{$trkpt['lon']}]";
				$i++;
			}
		}
		$trk = $trk; // !!!
		$trkJs .= "]";
 
   // calcul du centre de la trace pour centrer l'affichage et du format auto
		$maxLat = -90;	
		$minLat = 90;
		$maxLon = -180;
		$minLon = 180;
		if (isset($trk))
         foreach($trk as $i =>$trkpt) {
            if ($trkpt['lat']>$maxLat) $maxLat = $trkpt['lat'];
            if ($trkpt['lat']<$minLat) $minLat = $trkpt['lat'];
            if ($trkpt['lon']>$maxLon) $maxLon = $trkpt['lon'];
            if ($trkpt['lon']<$minLon) $minLon = $trkpt['lon'];
         }
      else {
         if (isset($gpx->wpt)) {
            foreach ($gpx->wpt as $unWpt) {
            if ($unWpt['lat']>$maxLat) $maxLat = $unWpt['lat'];
            if ($unWpt['lat']<$minLat) $minLat = $unWpt['lat'];
            if ($unWpt['lon']>$maxLon) $maxLon = $unWpt['lon'];
            if ($unWpt['lon']<$minLon) $minLon = $unWpt['lon'];
            }
         }
      }
      if (isset($trk)||isset($gpx->wpt)) {
         $centreLat = ($maxLat+$minLat)/2;
         $centreLon = ($maxLon+$minLon)/2;
         // format portrait ou paysage 
         $ecartLatMetres = distance($maxLat, $centreLon, $minLat, $centreLon);
         $ecartLonMetres = distance($centreLat, $maxLon, $centreLat, $minLon);
         if ($ecartLatMetres>$ecartLonMetres) $formatAuto = 'portrait';
         else  $formatAuto = 'paysage';

         if (isset($_POST['zoom'])) $zoom = $_POST['zoom'];
			else $zoom = ZOOM;
      }
   }
	///////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////
   function taIGN25() { // retourne une chaîne = contenu du fichier tableau d'assemblage des cartes IGN KML
	///////////////////////////////////////////////////////////////////////////////
		$fichierKml = 'ign25/ta25k.kml';
		$fp = fopen($fichierKml, "rb");
		clearstatcache();
		$chaineKml = fread($fp, filesize($fichierKml));
		fclose($fp);
		return $chaineKml;
	}
	///////////////////////////////////////////////////////////////////////////////
	
/*	
	///////////////////////////////////////////////////////////////////////////////
   function preparerCarteWpt($gpx) {
	///////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////
		global $chaineWptKml, $fichierWptKmlCheminWeb;

		// construction du fichier tmp/trkWpt.kml
		// -> couche KML pour  faire afficher les noms des waypoints avec showPointNames
		// icône transparente car l'icône est affichée dans la couche GPX
		// construction de la chaîne chaineWptKml
		// en-tête:
		$chaineWptKml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?> 
<kml xmlns="http://earth.google.com/kml/2.0"> 
<Document>
EOT;
		// style des waypoints (image transparente) transparent.png
		$chaineWptKml .= <<<EOT
<Style id="wpt">
	<IconStyle> 
		<Icon> <href>images/transparent.png</href></Icon> 
		<hotSpot x="10" y="10" xunits="pixels" yunits="pixels"></hotSpot>
	</IconStyle>
</Style> 
EOT;

		// les waypoints
		$i = 0;
		foreach ($gpx->wpt as $unWpt) {
			//$unWpt['lat'] $unWpt['lat'] $unWpt->name
			$unWpt['lon'] = (float) $unWpt['lon'];
			$unWpt['lat'] = (float) $unWpt['lat'];
			// décalage pour éviter la superposition avec l'affichage des noms des points kilométriques
//			$unWpt['lon'] += -0.000165;
//			$unWpt['lat'] += 0.000165;
			$chaineWptKml .= <<<EOT
<Placemark>
	<name>{$unWpt->name}</name> 
	<styleUrl> #wpt</styleUrl> 
	<description>point de passage: {$unWpt->name}</description>
	<Point>
	<coordinates>
			{$unWpt['lon']}, {$unWpt['lat']}
	</coordinates>
	</Point> 
</Placemark>
EOT;
		}
		
		// fin
		$chaineWptKml .= <<<EOT
</Document>
</kml>	
EOT;
      
      // enregistrer le fichier KML des wpt dans /tmp
		$nomFichierTemp = mt_rand(0,1000000);
		$fichierGpxCheminComplet = realpath(".")."/tmp/".$nomFichierTemp.".kml";
		// création du fichier sur le serveur
		$leFichier = fopen($fichierGpxCheminComplet, "x+");
		fwrite($leFichier,$chaineWptKml);
		fclose($leFichier);
		// on vérifie que le fichier a bien été créé
		$t_infoCreation['fichierCreer'] = false;
		if(file_exists($fichierGpxCheminComplet)!=true){
			die("on ne peut pas créer le fichier kml: ".$fichierGpxCheminComplet);
		}

		// on applique les permission au fichier créé
		$retour = chmod($fichierGpxCheminComplet,intval("0777",8));

		// préparation de la création de la couche KML
		$repSelf = $_SERVER['PHP_SELF'];
		$pos = strpos($repSelf, "visualiserGpx.php");
		$repSelf = substr($repSelf,0,$pos);

		$fichierWptKmlCheminWeb = $repSelf."tmp/".$nomFichierTemp.".kml";
   }
	///////////////////////////////////////////////////////////////////////////////
	
	///////////////////////////////////////////////////////////////////////////////
   function preparerCarteSymboles($trk) {
      global $chaineSymbolesKml, $fichierSymbolesKmlCheminWeb;

		// ajouter flèche et drapeaux
		// à chaque km placer un point sur la trace et une flèche à côté ; un drapeau au point de départ
		$j = 0;
		$distanceCumul = 0;
		$m = 1000;
		$cmpt = count($trk)-1;
		foreach($trk as $i =>$trkpt) {
			if ($i<$cmpt) {
				$d = distance ($trkpt['lat'],$trkpt['lon'],$trk[$i+1]['lat'],$trk[$i+1]['lon']);
				$distanceCumul += $d;
				if ($distanceCumul/$m>=1) {
					// azimut
					$a = azimut($trk[$i-1],$trkpt);
					$n = round($a/22.5);
					//flèche
					if($n==16) $n = 0;
					$ptFleche[$j]['style'] = "i".$n;
					// 2" à droite ~ 180m
					$aRad = ($a+90)*M_PI/180;
					$ptFleche[$j]['lat'] = $trk[$i]['lat']+cos($aRad)*1/3600;
					$ptFleche[$j]['lon'] = $trk[$i]['lon']+sin($aRad)*1/3600;
					// point rouge
					$pt[$j]['lat'] = $trk[$i]['lat'];
					$pt[$j]['lon'] = $trk[$i]['lon'];
					$pt[$j]['style'] = "pt";
					$j++;
					$m += 1000;
				}
			}
		}
		// ajouter un drapeau au départ de la trace
		$drp[0]['lat'] = $trk[0]['lat'];
		$drp[0]['lon'] = $trk[0]['lon'];
		$drp[0]['style'] = "depart";
		// ajouter un drapeau à l'arrivée de la trace
		$drp[1]['lat'] = $trk[$i]['lat'];
		$drp[1]['lon'] = $trk[$i]['lon'];
		$drp[1]['style'] = "arrivee";

		// construction du fichier symboles.kml contenant les flèches et les drapeaux 
		// pour affichage de la couche de type kml ; puis enregistrement du fichier
		// construction de la chaîne chaineSymbolesKml

		// en-tête:
		$chaineSymbolesKml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?> 
<kml xmlns="http://earth.google.com/kml/2.0"> 
<Document>

EOT;
		// styles drapeaux

		// style point
		$chaineSymbolesKml .= <<<EOT
<Style id="pt">
<IconStyle> <Icon> <href>images/pt.png</href> </Icon></IconStyle>
</Style> 
	
EOT;

		// styles flèches
		for  ($i = 0; $i <= 15; $i++) {
			$chaineSymbolesKml .= <<<EOT
<Style id="i$i">
<IconStyle> <Icon> <href>images/i$i.png</href> </Icon></IconStyle>
</Style> 
	
EOT;
		}
		// styles drapeaux
			$chaineSymbolesKml .= <<<EOT
<Style id="depart">
<IconStyle> 
	<Icon> <href>images/depart.png</href> </Icon>
	<hotSpot x="-1" y="0" xunits="pixels" yunits="pixels"></hotSpot>
</IconStyle>
</Style> 
<Style id="arrivee">
<IconStyle> 
	<Icon> <href>images/arrivee.png</href> </Icon>
	<hotSpot x="30" y="0" xunits="pixels" yunits="pixels"></hotSpot>
</IconStyle>
</Style> 
	
EOT;
		
		// placemarks points
			foreach ($pt AS $j => $unPt) {
		$km = $j+1;
		$chaineSymbolesKml .= <<<EOT
<Placemark>
<name> km $km</name> 
<description>$km km</description>
<styleUrl> #pt</styleUrl> 
<Point>
<coordinates>
		{$unPt['lon']}, {$unPt['lat']}
</coordinates>
</Point> 
</Placemark>

EOT;
			}
		
		// placemarks flèches
			foreach ($ptFleche AS $j => $unPtFleche) {
		$chaineSymbolesKml .= <<<EOT
<Placemark>
<name></name> 
<description></description>
<styleUrl> #{$unPtFleche['style']}</styleUrl> 
<Point>
<coordinates>
		{$unPtFleche['lon']}, {$unPtFleche['lat']}
</coordinates>
</Point> 
</Placemark>

EOT;
			}

			// placemark drapeaux
			foreach ($pt AS $j => $unPt) {
		$chaineSymbolesKml .= <<<EOT
<Placemark>
<name></name> 
<description></description>
<styleUrl> #{$drp[0]['style']}</styleUrl> 
<Point>
<coordinates>
		{$drp[0]['lon']}, {$drp[0]['lat']}
</coordinates>
</Point> 
</Placemark>
<Placemark>
<name></name> 
<description></description>
<styleUrl> #{$drp[1]['style']}</styleUrl> 
<Point>
<coordinates>
		{$drp[1]['lon']}, {$drp[1]['lat']}
</coordinates>
</Point> 
</Placemark>

EOT;
			}
		
		// fin
		$chaineSymbolesKml .= <<<EOT
</Document>
</kml>	
EOT;

{
		///////////////////////////////////////////////////////////////////////////////
		// enregistrer le fichier KML dans /tmp
		$nomFichierTemp = mt_rand(0,1000000);
		$fichierGpxCheminComplet = realpath(".")."/tmp/".$nomFichierTemp.".kml";
		// création du fichier sur le serveur
		$leFichier = fopen($fichierGpxCheminComplet, "x+");
		fwrite($leFichier,$chaineSymbolesKml);
		fclose($leFichier);
		// on vérifie que le fichier a bien été créé
		$t_infoCreation['fichierCreer'] = false;
		if(file_exists($fichierGpxCheminComplet)!=true){
			die("on ne peut pas créer le fichier kml: ".$fichierGpxCheminComplet);
		}

		// on applique les permission au fichier créé
		$retour = chmod($fichierGpxCheminComplet,intval("0777",8));

		// préparation de la création de la couche KML
		$repSelf = $_SERVER['PHP_SELF'];
		$pos = strpos($repSelf, "visualiserGpx.php");
		$repSelf = substr($repSelf,0,$pos);

		$fichierSymbolesKmlCheminWeb = $repSelf."tmp/".$nomFichierTemp.".kml";
}
   }
	///////////////////////////////////////////////////////////////////////////////

*/	
	///////////////////////////////////////////////////////////////////////////////
   function preparerCarteSansFichierGpx() {
      global $fichierGpxCheminWeb, $fichierSymbolesKmlCheminWeb, $fichierWptKmlCheminWeb, $fichierWptKMLCheminWeb, $centreLat, $centreLon, $zoom, $formatAuto;

      $formatAuto = "";
		$fichierGpxCheminWeb = ""; // pas de trace à afficher
		$fichierSymbolesKmlCheminWeb = ""; // pas de symboles à afficher
		$fichierWptKMLCheminWeb = ""; // pas de point noms de waypoints à afficher
		// alors on centre sur Marseilleveyre
		//5.362453,43.217581
		
		// cookies de centre de la carte ?
		if (isset($_COOKIE['lat']) && isset($_COOKIE['lon'])) {
			$centreLat = $_COOKIE['lat'];
			$centreLon = $_COOKIE['lon'];
		}
		else {
			$centreLat = LAT_CENTRE;
			$centreLon = LON_CENTRE;
		}

		if (isset($_POST['zoom'])) $zoom = $_POST['zoom'];
		else $zoom = ZOOM;
		// log
		enregistrerLog ('gpxRando', 'affichage de la carte IGN', '');

   }
	///////////////////////////////////////////////////////////////////////////////


	///////////////////////////////////////////////////////////////////////////////
   function titreDeLaPage() {
	///////////////////////////////////////////////////////////////////////////////
		$titre = "gpx2tdm Rando sur carte IGN"; // titre par défaut si pas de fichier gpx
	// depuis index.php: nom du fichier gpx sans extension
		if (isset($_FILES['fichierGpx']['name'])) {
			$titre = substr($_FILES['fichierGpx']['name'],0,-4);
		}
	// depuis gpx2tdm.php = nom du fichier gpx sans extension
		if (isset($nomCouche)) {
			$titre = $nomCouche;
		}
      return $titre;
   }
		
   ///////////////////////////////////////////////////////////////////////////////
   function nettoyerTmp() {
   ///////////////////////////////////////////////////////////////////////////////
      $extension_choisie = "gpx";
      $age_requis = 900; // 15 minutes
      $dossier_traite = realpath(".")."/tmp";

      // On ouvre le dossier.
      $repertoire = opendir($dossier_traite);
      // On lance notre boucle qui lira les fichiers un par un.
      while(false !== ($fichier = readdir($repertoire)))
      {
         // On met le chemin du fichier dans une variable simple
         $chemin = $dossier_traite."/".$fichier;
         // Les variables qui contiennent toutes les infos nécessaires.
         $infos = pathinfo($chemin);
         $extension = $infos['extension'];
         $age_fichier = time() - filemtime($chemin);
         // On n'oublie pas LA condition sous peine d'avoir quelques surprises.:p
         if($fichier!="." AND $fichier!=".." AND !is_dir($fichier) AND
         $extension == $extension_choisie AND $age_fichier > $age_requis)
         {
            $res = unlink($chemin);
         }
      }
      closedir($repertoire); // On ferme !
	}




?>

<!DOCTYPE html>
<html lang="fr-fr">
	<head>
	<title><?php echo($titre);?></title>
	
		<!-- Library Leaflet 1.6-->
		<link href="css/leaflet.css" rel="stylesheet" type="text/css" />
		<script src="js/leaflet.js"></script>

		<!-- Library GpPluginLeaflet-src.js pour le gestionnaire de couches et les coordonnées du curseur-->
		<link href="css/GpPluginLeaflet.css" rel="stylesheet" type="text/css" />
		<script src="js/GpPluginLeaflet.js"></script>

		<!-- pour UTM grid -->
		<script src="js/proj4.js"></script>
		<script src="js/Leaflet.MetricGrid.js"></script>
		
		<!-- pour KML Leaflet.KML.js pour le tableau d'assemblage des cartes IGN-->
		<script src="js/Leaflet.KML.js"></script>
		
		<!-- pour imprimer directement la carte : Leaflet.EasyPrint.js Pb : voir plus bas
		<script src="js/bundle.js"></script>-->
		
		<!-- pour imprimer l'aide'-->
		<script type="text/javascript">
			function printDiv(eleId){
				var PW = window.open('', '_blank', 'Print content');
				PW.document.write(document.getElementById(eleId).innerHTML);
				PW.document.close();
				PW.focus();
				PW.print();
				PW.close();
			}
		</script>
		
		<!-- pour enregistrer le centre de la page par cookie -->
		<script src="js/carte.js"></script>
		

		
		<style type="text/css">
		<!--
			body {font-family:sans-serif; font-size:x-small;}
			h1 {color: #333399;}
			h2 {color: #333399;}
			hr {color: #333399;}
			
			table  {  text-align: left; width: 100%; border-width:thin; border-color:blue; empty-cells:show;border-collapse:collapse; }
			td {border-width:thin; border-color:blue; empty-cells:show;border-collapse:collapse; font-family:sans-serif; font-size:x-small; vertical-align: middle;} 
			input, textarea, select {font-family:sans-serif; font-size:x-small; background-color:#CCCCFF; }
	
			div#mapDiv {
				position: absolute;
				top: 0px;
				left: 0px;
				background-color:white;
				width: <?php if ($_POST['modeVisualiser']=="portrait" || ($_POST['modeVisualiser']=="auto" && $formatAuto=="portrait")) echo "1432"; else echo "2055";?>px;  
				height: <?php if ($_POST['modeVisualiser']=="portrait" || ($_POST['modeVisualiser']=="auto" && $formatAuto=="portrait")) echo "2055"; else echo "1432";?>px; 
			}
			
				div#aide {
					position: absolute;
					top: 20%;
					right: 20%;
					z-index: 10001;
					width: 60%;
					height: 60%;
					padding: 0px;
					background-color:white;
					border-width: 3px;
					border-color: #336699;
					border-style: solid;
					display: inline;
					overflow: hidden; /* hidden*/
					font-family:sans-serif; font-size:small;
					text-align: justify;
				}

				div#contenuAide {
/*					position: relative;
					top: 0px;
					left: 0px;
					right: 50px;
					bottom: 0px
*/
					width: 97%;
					height: 85%; /*95% */
					padding-top: 0px;
					padding-left: 10px;
					padding-right: 10px;
					padding-bottom: 10px;

					overflow: auto;
					font-family:sans-serif; font-size:small;
					text-align: justify;
				}

				div#aide > p {
					margin-top: 2px; margin-bottom: 2px;
				}

				div#aide td {
					font-family:sans-serif; font-size:small;
				}

				div#aide > ul {
					margin-top: 2px; margin-bottom: 2px;
				}

				div#aide > ol {
					margin-top: 2px; margin-bottom: 2px;
				}

				div#aide > h1{
					color: #336699;
					margin-top: 8px; margin-bottom: 5px;
				}
				div#aide > h2{
					color: #336699;MetricGrid
					margin-top: 8px; margin-bottom: 5px;

				}
				div#aide > h3{
					color: #336699;
					margin-top: 8px; margin-bottom: 5px;
				}
				div#aide > h4{
					color: #336699;
					margin-top: 8px; margin-bottom: 5px;
				}
				div#aide > hr{
					color: #336699;
					height: 3px;
				}

				td {
					border-top: 1px dotted black;
					border-bottom: 1px dotted black;
				}

				.leaflet-tooltip {
					background: rgb(235, 235, 235);
					background: rgba(235, 235, 235, 0.81);
					background-clip: padding-box;
					border-color: #777;
					border-color: rgba(0,0,0,0.25);
					border-radius: 4px;
					border-style: solid;
					border-width: 4px;
					color: #111; /* #111*/
					display: block;
					font: 12px/20px "Helvetica Neue", Arial, Helvetica, sans-serif;
					font-weight: bold;
					padding: 1px 6px;
					position: absolute;
					-webkit-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
							user-select: none;
					pointer-events: none;
					white-space: nowrap;
					z-index: 6;
				}

		-->
		</style>
		

	</head>
	<body>
<!--	
		<div align=center>
-->		
			<div id="mapDiv">
			</div>
			
			<canvas id=canvasWptSymbol width=100% height=100%>
			</canvas>

			<canvas id=canvasName width=100% height=100%>
			</canvas>

			<div id="aide"
				style="display:inline; " title="Mode d'emploi">
				<a id="0"></a>
				
				<div id="barre" style="width:100%; height:26px; background-color:#336699;">
					<img src="images/print.png" width="16" height="16" border="0" alt="Imprimer ce mode d'emploi" title="Imprimer ce mode d'emploi"
					style="position:absolute; top:5px; right:23px; float:right;"
					onClick ="printDiv('contenuAide');">
					<img src="images/close.png" width="16" height="16" border="0" alt="Fermer" title="Fermer"
					style="position:absolute; top:5px; right:5px; float:right;"
					onClick="document.getElementById('aide').style.display='none';	">
				</div>
				
				<div id="contenuAide">
					<h1>Imprimer la carte : Mode d'emploi</h1>

					<h2>Afficher/cacher les couches</h2>
					<p style="margin-left: 40px;">Le gestionnaire de couches <img alt="" src="images/layerSwitcher.png"  style="vertical-align:middle;" > permet d'afficher ou de masquer chacune des couches disponibles:
					</p>
					<div style="text-align: center;"><img alt="" src="images/layerSwitcherMaximised.png"><br>
					</div>
					<p style="margin-left: 40px;">Vous pouvez choisir entre les deux fonds de carte disponibles : carte IGN affichée par défaut et OpenStreetMap Topo.
					</p>
					<p style="margin-left: 40px;">En cliquant sur les doubles flèches vers le bas d'une couche <img alt="" src="images/doublesFleches.png"  style="vertical-align:middle;" >, on peut modifier l'opacité de la couche.
					</p>

					<h2>Afficher/cacher les différentes boîtes</h2>
					<p style="margin-left: 40px;">Avant de capturer la page pour l'imprimer, il
						est souhaitable de cacher les différentes boîtes ainsi que cette fenêtre d'aide.</p>
					<ul>
						<li><span style="font-weight: bold;">ALT+a</span>: afficher/cacher cette
						aide</li>
						<li><span style="font-weight: bold;">ALT+x</span>: afficher/cacher les
						boîtes : zoom, gestionnaire de couche, corrdonnées du curseur, échelle</li>
					</ul>

					<h2>Enregistrer les coordonnées du centre de la page</h2>
					<p style="margin-left: 40px;">Vous pouvez enregistrer les coordonnées du centre de la page dans des cookies afin de retrouver cette position de la carte lors d'une session suivante.</p>
					<ul>
						<li><span style="font-weight: bold;">ALT+p</span>: enregistrer les coordonnées du centre de la page</li>
					</ul>
					<h2>Carroyage UTM</h2>
					<p style="margin-left: 40px;">Le carroyage UTM kilométrique représenté par des lignes bleues est visible aux niveaux de zoom correspondant aux fonds de carte IGN au 1/25000 pour les zones 30, 31 et 32 (France métropolitaine).</p>
					<p style="margin-left: 40px;">Les coordonées UTM des lignes se trouvent sur le bord gauche et sur le bord inférieur de la carte (en km). Entre parenthèses, on trouve à gauche la lettre correspondant à la bande et en bas le nombre correspondant à la zone.</p>

					<h2>Recherche des numéros de carte IGN</h2>
					<p style="margin-left: 40px;">On peut faire afficher le tableau d'assemblage des cartes IGN au 1/25000.</p>
					<p style="margin-left: 40px;">Les limites des cartes sont représentées par des traits bleus épais.</p>

					<ul>
						<li><span style="font-weight: bold;">ALT+i</span>: afficher/cacher le tableau d'assemblage des cartes IGN (caché par défaut)</li>
						<li><span style="font-weight: bold;">Clic gauche</span> sur une zone : affiche le numéro et le nom de la carte.</li>
					</ul>
					
					<h2>Afficher/cacher les informations liées à la trace GPX</h2>
					<p style="margin-left: 40px;">On peut faire afficher ou cacher les noms des waypoints et les nombres de kilomètres le long de la trace.</p>

					<ul>
						<li><span style="font-weight: bold;">ALT+t</span>: afficher/cacher les noms des waypoints (cachés par défaut)</li>
						<li><span style="font-weight: bold;">ALT+0</span> (zéro) : afficher/cacher les nombres de kilomètres  (affichés par défaut)</li>
					</ul>
					
					<h2>Résolution d'impression pour une carte au 1/25000</h2>
					<p>Cette page permet d'afficher une carte sur fond 1/25000 (grossissement minimum) imprimable au format A4 en mode portrait ou paysage selon la commande passée.</p>
					<p>L'échelle de la carte varie en fonction de la latitude du fait de la projection utilisée par Géoportail. Il faudra donc choisir la résolution d'impression en fonction de la latitude moyenne de la carte pour obtenir une carte approximativement au 1/25000.
					</p>
					<p style="font-style: italic;">
						Merci à Jean Guillo pour ses remarques sur la variation de l'échelle et pour la solution proposée: résolution d'impression en pixels par pouce = 132.77 / cos(latitude).
					</p>
					<p>Pour obtenir la résolution d'impression, placer le curseur dans la
						carte (si possible à mi-hauteur) puis faire <span style="font-weight: bold;">ALT
							+ r</span>: la résolution à appliquer s'affiche dans une fenêtre:</p>
					<div style="text-align: center;"><img alt="" src="images/resolution.png"><br>
					</div>

					<h2>Procédure pour imprimer la carte au 1/25000</h2>
					<p style="margin-left: 40px;">Il est nécessaire de disposer d'un outil de
					capture de la page entière:</p>
					<ul>
						<li> pour Mozilla Firefox, on peut installer l'extension <a target="_blank" href="https://addons.mozilla.org/fr/firefox/addon/pagesaver-we/">Page Saver WE</a> &nbsp;<img alt="" src="images/pageSaverWE.png" style="height: auto; width: 30px; vertical-align:middle;">
							&nbsp; </li>
						<li>&nbsp;pour Chromium et Google Chrome, on peut installer l'extension Nimbus Screenshot & Screen Video Recorder
							<a target="_blank" href="https://chrome.google.com/webstore/detail/nimbus-screenshot-screen/bpconcjcammlapcogcnnelfmaeghhagj">Nimbus Screenshot Screen Video Recorder</a>&nbsp; <img alt="" src="images/NimbusScreenshot.png" style="height: auto; width: 30px; vertical-align:middle;"></li>
					</ul>
					<ol>
						<li>Revenez si nécessaire au facteur de zoom initial: 1/25000
							grossissement minimum </li>
						<li>Éventuellement <span style="font-weight: bold;">ALT+x</span> pour
							fermer toutes les boîtes.</li>
						<li>Activez l'outil de capture de page entière en choisissant
							d'enregistrer l'image de la page entière.</li>
						<li>Ouvrez l'image avec une application de traitement d'image qui permet
							de choisir la résolution d'impression (telle que <a target="_blank" href="http://www.gimp.org/downloads/">Gimp</a>)
							; imprimez en choisissant l'orientation de la page et la résolution
							d'impression qui conviennent.</li>
					</ol>
						
				</div> 
				<hr>
			</div>  
			</div>
<!--

		</div>  
-->
		
			<script type="text/javascript">
				function go() {
					// carte centrée et zoomée
					map = L.map('mapDiv', {
						center: [<?php echo $centreLat; ?>, <?php echo $centreLon; ?>],
						zoom: <?php echo $zoom; ?>,
						attributionControl: true, // pour empêcher le bug des attributions des photos aériennes, on bloque toutes les attributions ! À supprimer quand le bug sera corrigé.
						zoomControl: true
					});
/*
	osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="https://osm.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors, <a href="https://osm.org/copyright" target="_blank" rel="noopener">ODbL 1.0</a>'}),
	
	otm = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="https://osm.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors, <a href="https://opentopomap.org" target="_blank" rel="noopener">OpenTopoMap</a>', maxZoom: 17}),
	
	ocm = L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey=6a06aa8f11e4452f9b3c30933483eb62', {attribution: 'Maps &copy; <a href="https://www.thunderforest.com/" target="_blank" rel="noopener">Thunderforest</a>, data &copy; <a href="http://www.osm.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'}),
	
	esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {attribution: 'Tiles &copy; Esri'}),
	
	esat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {attribution: 'Tiles &copy; Esri'}),
	
	ign = L.tileLayer('https://wxs.ign.fr/ph1huhwbb5yrxdms4zh90g6g/geoportail/wmts?LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS&EXCEPTIONS=text/xml&FORMAT=image/jpeg&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '&copy;IGN <a href="https://www.geoportail.fr/" target="_blank" rel="noopener"><img src="/img/geoportail.gif" style="height: 12px;width: 57px;"></a> <a href="https://www.geoportail.gouv.fr/mentions-legales" alt="TOS" title="TOS" target="_blank" rel="noopener">Terms of Service</a>'}),
	
	top25 = L.tileLayer('https://wxs.ign.fr/ph1huhwbb5yrxdms4zh90g6g/geoportail/wmts?LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS.SCAN25TOUR&EXCEPTIONS=text/xml&FORMAT=image/jpeg&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '&copy;IGN <a href="https://www.geoportail.fr/" target="_blank" rel="noopener"><img src="/img/geoportail.gif" style="height: 12px;width: 57px;"></a> <a href="https://www.geoportail.gouv.fr/mentions-legales" alt="TOS" title="TOS" target="_blank" rel="noopener">Terms of Service</a>', minZoom: 6, maxZoom: 16}),
	
	cad = L.tileLayer('https://wxs.ign.fr/ph1huhwbb5yrxdms4zh90g6g/geoportail/wmts?LAYER=CADASTRALPARCELS.PARCELS&EXCEPTIONS=text/xml&FORMAT=image/png&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '&copy;IGN <a href="https://www.geoportail.fr/" target="_blank"><img src="/img/geoportail.gif" style="height: 12px;width: 57px;"></a> <a href="https://www.geoportail.gouv.fr/mentions-legales" alt="TOS" title="TOS" target="_blank" rel="noopener">Terms of Service</a>'}),
	
	sat = L.tileLayer('https://wxs.ign.fr/ph1huhwbb5yrxdms4zh90g6g/geoportail/wmts?LAYER=ORTHOIMAGERY.ORTHOPHOTOS&EXCEPTIONS=text/xml&FORMAT=image/jpeg&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '&copy;IGN <a href="https://www.geoportail.fr/" target="_blank" rel="noopener"><img src="/img/geoportail.gif" style="height: 12px;width: 57px;"></a> <a href="https://www.geoportail.gouv.fr/mentions-legales" alt="TOS" title="TOS" target="_blank" rel="noopener">Terms of Service</a>'}),
	
	pente = L.tileLayer('https://wxs.ign.fr/ph1huhwbb5yrxdms4zh90g6g/geoportail/wmts?LAYER=GEOGRAPHICALGRIDSYSTEMS.SLOPES.MOUNTAIN&EXCEPTIONS=text/xml&FORMAT=image/png&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}', {attribution: '&copy;IGN <a href="https://www.geoportail.fr/" target="_blank" rel="noopener"><img src="https://api.ign.fr/geoportail/api/js/2.0.0beta/theme/geoportal/img/logo_gp.gif" style="height: 12px;width: 57px;"></a> <a href="https://www.geoportail.gouv.fr/mentions-legales" alt="TOS" title="TOS" target="_blank" rel="noopener">Terms of Service</a>'}),
	
	swt = L.tileLayer('https://wmts10.geo.admin.ch/1.0.0/ch.swisstopo.pixelkarte-farbe/default/current/3857/{z}/{x}/{y}.jpeg', {attribution: '&copy; SwissTopo'}),
	
	esp = L.tileLayer('https://www.ign.es/wmts/mapa-raster?request=getTile&layer=MTN&TileMatrixSet=GoogleMapsCompatible&TileMatrix={z}&TILEROW={y}&TILECOL={x}&format=image/jpeg', {attribution: '&copy; ign.es'}),
	
	be = L.tileLayer('https://www.ngi.be/cartoweb/1.0.0/topo/default/3857/{z}/{y}/{x}.png', {attribution: '&copy; ngi.be', minZoom: 7, maxZoom: 17})
	
	aut = L.tileLayer('https://maps4.wien.gv.at/basemap/bmapgrau/normal/google3857/{z}/{y}/{x}.png', {attribution: 'Tiles &copy; <a href="https://www.basemap.at" target="_blank" rel="noopener">basemap.at</a>', minZoom: 1, maxZoom: 18})

 					
					// layer Swiss Topo
					var lyrSwissTopo = new L.tileLayer('https://wmts20.geo.admin.ch/1.0.0/ch.swisstopo.pixelkarte-farbe/default/current/3857/{z}/{x}/{y}.jpeg', {
						opacity: 1
					}) ;
*/
					
					// layer OSM topo
					var lyrOsmTopo= L.tileLayer('https://opentopomap.org/{z}/{x}/{y}.png', {
						opacity: 1
					}) ;

					// layer IGN map
					var lyrIgnMaps = L.tileLayer(
						"https://wxs.ign.fr/<?php echo CLE_GEOPORTAIL; ?>/geoportail/wmts?" +
						"&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0" +
						"&STYLE=normal" +
						"&TILEMATRIXSET=PM" +
						"&FORMAT=image/jpeg"+
						"&LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS"+
						"&TILEMATRIX={z}" +
						"&TILEROW={y}" +
						"&TILECOL={x}",
						{
							minZoom : 0,
							maxZoom : 18,
							attribution : "IGN-F/Geoportail",
							tileSize : 256, // les tuiles du Géooportail font 256x256px
							opacity : 1
						}
					);

					
					// layer IGN ortho 
					var lyrIgnOrtho = L.tileLayer(
						"https://wxs.ign.fr/<?php echo CLE_GEOPORTAIL; ?>/geoportail/wmts?" +
						"&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0" +
						"&STYLE=normal" +
						"&TILEMATRIXSET=PM" +
						"&FORMAT=image/jpeg"+
						"&LAYER=ORTHOIMAGERY.ORTHOPHOTOS"+
						"&TILEMATRIX={z}" +
						"&TILEROW={y}" +
						"&TILECOL={x}",
						{
							minZoom : 0,

							maxZoom : 20,
							attribution : "IGN-F/Geoportail",
							tileSize : 256, // les tuiles du Géooportail font 256x256px
							opacity : 0.5 // 0
						}
					);
					
					// layer IGN boundaries
					var lyrIgnBoundaries = L.tileLayer(
						"https://wxs.ign.fr/<?php echo CLE_GEOPORTAIL; ?>/geoportail/wmts?" +
						"&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0" +
						"&STYLE=normal" +
						"&TILEMATRIXSET=PM" +
						"&FORMAT=image/png"+
						"&LAYER=ADMINISTRATIVEUNITS.BOUNDARIES"+
						"&TILEMATRIX={z}" +
						"&TILEROW={y}" +
						"&TILECOL={x}",
						{
							minZoom : 0,
							maxZoom : 18,
							attribution : "IGN-F/Geoportail",
							tileSize : 256, // les tuiles du Géooportail font 256x256px
							opacity : 1 // 0
						}
					);
					
					// layer IGN CADASTRALPARCELS
					var lyrIgnParcels = L.tileLayer(
						"https://wxs.ign.fr/<?php echo CLE_GEOPORTAIL; ?>/geoportail/wmts?" +
						"&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0" +
						"&STYLE=normal" +
						"&TILEMATRIXSET=PM" +
						"&FORMAT=image/png"+
						"&LAYER=CADASTRALPARCELS.PARCELS"+
						"&TILEMATRIX={z}" +
						"&TILEROW={y}" +
						"&TILECOL={x}",
						{
							minZoom : 0,
							maxZoom : 19,
							attribution : "IGN-F/Geoportail",
							tileSize : 256, // les tuiles du Géooportail font 256x256px
							opacity : 0.5 // 0
						}
					);
	
	
				
					
					// gpx si existe lyrTrk
<?php
	if ($fichierGpxCheminWeb!='') {
?>
					var trkPt = <?php echo $trkJs; ?>;
					lyrTrk = L.polyline(trkPt, {color: 'blue', weight: 10, opacity: 0.3});
<?php
	}
?>
					
					// grilles UTM 30 31 32 
					var u30Grid = L.utmGrid(30, false, {
						color: 'blue',
						latLonClipBounds: [[42.67, -180 + (29 * 6)],[49.73, -180 + (29 * 6) + 6]], //[[0, -180 + (29 * 6)],[80, -180 + (29 * 6) + 6]]
						drawClip: false, 
						showAxisLabels: [1000], // 100, 1000, 10000, 100000
						showSquareLabels: [], // 100000 label 100km grid squares
						showAxis100km: false // true
					});
					
					var u31Grid = L.utmGrid(31, false, {
						color: 'blue', //#080
						latLonClipBounds: [[42.32, -180 + (30 * 6)],[51.1, -180 + (30 * 6) + 6]], //[[0, -180 + (30 * 6)],[80, -180 + (30 * 6) + 6]]
						drawClip: false,   // true      
						showAxisLabels: [1000], // 100, 1000, 10000, 100000
						showSquareLabels: [], // 100000 label 100km grid squares
						showAxis100km: false // true
					});
					var u32Grid = L.utmGrid(32, false, {
						color: 'blue',
						latLonClipBounds: [[41.32, -180 + (31 * 6)],[49.57, -180 + (31 * 6) + 6]], //[[0, -180 + (31 * 6)],[80, -180 + (31 * 6) + 6]]
						drawClip: false,         
						showAxisLabels: [1000], // 100, 1000, 10000, 100000
						showSquareLabels: [], // 100000 label 100km grid squares
						showAxis100km: false // true
					});
					

				
				
					// ajout des couches: ordre inverse
					map.addLayer(u30Grid) ;
					map.addLayer(u31Grid) ;
					map.addLayer(u32Grid) ;
//					map.addLayer(lyrSwissTopo);
					map.addLayer(lyrOsmTopo) ;

					map.addLayer(lyrIgnMaps) ;

					map.addLayer(lyrIgnOrtho);
					map.addLayer(lyrIgnBoundaries) ;
					map.addLayer(lyrIgnParcels) ;

					
<?php
	if ($fichierGpxCheminWeb!='') {	
?>
						map.addLayer(lyrTrk);

<?php
	}
?>

					
					var layerSwitcher = L.geoportalControl.LayerSwitcher({
						layers: [
<?php
	if ($fichierGpxCheminWeb!='') { 
?>
							{
								layer: lyrTrk,
								display: true,
								config: {
									visibility: true,
									title: "Trace GPX"
								}
							},
<?php
	}
?>
/*
							{
								layer: lyrSwissTopo,
								display: true,
								config: {
									visibility: false,
									title: "Carte Swiss Topo"
								}
							},
*/
							{
								layer: lyrOsmTopo,
								display: true,
								config: {
									visibility: false,
									title: "Carte Open Street Maps Topo"
								}
							},
							{
								layer: lyrIgnMaps,
								display: true,
								config: {
									visibility: true,
									title: "Carte IGN"

								}
							},
							{
								layer: lyrIgnOrtho,
								display: true,
								config: {
									visibility: false,
									title: "Photos aériennes"

								}
							},
							{
								layer: lyrIgnBoundaries,
								display: true,
								config: {
									visibility: false,
									title: "Limites administratives"

								}
							},
							{
								layer: lyrIgnParcels,
								display: true,
								config: {
									visibility: false,
									title: "Parcelles cadastralles"

								}
							},
							{
								layer: u30Grid,
								display: false,
								config: {
									visibility: true
								}
							},
							{
								layer: u31Grid,
								display: false,
								config: {
									visibility: true
								}
							},
							{
								layer: u32Grid,
								display: false,
								config: {
									visibility: true
								}
							}
						],
						
						position: "topleft",
						collapsed: true
					});
					
					layerSwitcher.addTo(map);					
					// virer la couche parasite générée par GP avec un label numérique pour la trace
					document.getElementsByClassName('GPlayerBasicTools')[0].remove();
					
					// n° de carte IGN
					var kmlText = `<?php echo taIGN25(); ?>`;
					var parser = new DOMParser();
					var kml = parser.parseFromString(kmlText, 'text/xml');
					lyrNumIgn = new L.KML(kml);
					//lyrNumIgn.addTo(map);
					//map.addLayer(lyrNumIgn);
					//layerSwitcher.addLayer(lyrNumIgn,true,{visibility: true, title: "n° cartes IGN"});
					
<?php
	if ($fichierGpxCheminWeb!='') { 
?>
					// ajout des waypoints sous forme de markers avec tooltip
					var wptIcon = L.icon({
						iconUrl: 'images/waypoint.png',
						iconSize: [20, 20],
						tooltipAnchor: [10,0]
					});
					
					var wpt = []; //array des layers marker
					var i = 0;
<?php
		$chWptMarker = '';
		$existeWpt = FALSE;
		foreach ($gpx->wpt as $unWpt) {
			$existeWpt = TRUE;
			//$unWpt['lat'] $unWpt['lon'] $unWpt->name
			$name = addslashes($unWpt->name);
			$chWptMarker .= "	i = wpt.push(L.marker([{$unWpt['lat']},{$unWpt['lon']}], {icon: wptIcon, title: '$name'}));
						wpt[i-1].bindTooltip('$name',{permanent: true, opacity: 0.7, className: 'leaflet-tooltip'}); \n";
		}
		echo($chWptMarker);
?>
					layersWptGroup = L.layerGroup();
					for (var key in wpt) {
						layersWptGroup.addLayer(wpt[key]);
						//wpt[key].closeTooltip();
					}
					layersWptGroup.addTo(map);
					
					layersWptGroup.eachLayer(function (layer) {
						layer.closeTooltip();
					});
							
					// ajout des symboles (flèches kilométriques et drapeaux de départ et d'arrivée)
					// création des icônes array fleches
					var flecheIcon = [];
<?php
		$chFlecheIco = '';
		for ($i=0; $i<16; $i++) {
			$uneFlecheIco = "L.icon({iconUrl: 'images/i".$i.".png', iconSize: [19, 19], tooltipAnchor: [0,0]})";
			$chFlecheIco .= "flecheIcon.push($uneFlecheIco); ";			
		}
		echo $chFlecheIco;
?>
					// ajout des flèches sous forme de markers avec tooltip
<?php
		$chSymbolesMarker = 'symbole = []; ';
		// à chaque km placer une flèche numérotée 
		$j = 0;
		$distanceCumul = 0;
		$m = 1000;
		$cmpt = count($trk)-1;
		foreach($trk as $i =>$trkpt) {
			if ($i<$cmpt) {
				$d = distance ($trkpt['lat'],$trkpt['lon'],$trk[$i+1]['lat'],$trk[$i+1]['lon']);
				$distanceCumul += $d;
				if ($distanceCumul/$m>=1) {
					// azimut
					$a = azimut($trk[$i-1],$trkpt);
					$n = round($a/22.5);
					//flèche
					if($n==16) $n = 0;
					$ptFleche[$j]['style'] = "i".$n;
					
					$ptFleche[$j]['lat'] = $trk[$i]['lat'];
					$ptFleche[$j]['lon'] = $trk[$i]['lon'];
					
					$km = $j+1;
					$chSymbolesMarker .= "	i = symbole.push(L.marker([{$trk[$i]['lat']},{$trk[$i]['lon']}], {icon: flecheIcon[$n], title: '$km'}));
					symbole[i-1].bindTooltip('$km',{permanent: true, opacity: 0.7, className: 'leaflet-tooltip'}); \n";
					$j++;
					$m += 1000;
				}
			}
		}
?>
					// ajout des drapeaux sans tooltip
					var departIcon = L.icon({
						iconUrl: 'images/depart.png',
						iconSize: [32,30],
						iconAnchor: [0,30]
					});
;
					var arriveeIcon = L.icon({
						iconUrl: 'images/arrivee.png',
						iconSize: [32, 30],
						iconAnchor: [32,30]
					});
					
					
<?php
		$chSymbolesMarker .= "	symbole.push(L.marker([{$trk[0]['lat']},{$trk[0]['lon']}], {icon: departIcon})); \n";
		$chSymbolesMarker .= "	symbole.push(L.marker([{$trk[$i]['lat']},{$trk[$i]['lon']}], {icon: arriveeIcon})); \n";

		echo "$chSymbolesMarker\n";
?>
					layersSymbolGroup = L.layerGroup();
					for (var key in symbole) {
						layersSymbolGroup.addLayer(symbole[key]);
					}
					layersSymbolGroup.addTo(map);
<?php
	} // fin si gpx
?>
					
					// ajout du contrôle mousePositon
					var mousePosition = L.geoportalControl.MousePosition({
						position: 'topleft',
						collapsed: true,
						displayAltitude: true,
						editCoordinates: false,
						altitude: {
								triggerDelay: 100,
								responseDelay: 500,
								noDataValue: -99999,
								noDataValueTolerance: 90000,
								serviceOptions: {}
						},
						systems: [
							{
								crs: L.CRS.EPSG4326,
								label: "Lon,Lat",
								type: "Geographical"
							},
							{
								crs: L.geoportalCRS.EPSG2154,
								label: "Lambert 93",
								type: "Metric"
							}
						],
						units: ["DEC", "DMS"]
					});
					map.addControl(mousePosition);
					
					// ajout du contrôle de recherche
					var searchCtrl = L.geoportalControl.SearchEngine({
					});
					map.addControl(searchCtrl);	
					
					// ajout de l'échelle
					L.control.scale({position: 'topleft', metric: true, imperial: false}).addTo(map);
					
//	console.log(lyrTrk.getName());
/*					
map.eachLayer(function(layer){
    layer.bindPopup('Hello');
//	console.log( layer.getLatLng());
*/
/*
map.eachLayer(function(layer) {
    if(layer.options && layer.options.pane === "markerPane") {
        alert("Marker [" + layer.getLatLng() + " "+ layer.options.title +"]");
    }
});
*/
/*
					// ajout du contrôle easy print
					// pb OK pour la capture mais l'image est plus grande que la carte (zone transparente à éliminer)
					L.easyPrint({
						title: 'Imprimer',
						position: 'topleft',
						sizeModes: ['Current'],
						defaultSizeTitles: {Current: 'Toute la page', A4Landscape: 'A4 Paysage', A4Portrait: 'A4 Portrait'},
					   exportOnly: true
					}).addTo(map);
*/
					

					document.body.style.cursor='default';
				} // fin fonction go
				
				
				// existence du fichier autoconf
<?php
	if (file_exists('inc/autoconf.json'))  {
?>
				var autoconf = true;
<?php
	}
	else {
?>
				var autoconf = false;
<?php
	}

?>
				
				if (!autoconf) { // pas d'AUTOCONF'
					window.onload = function () {
							document.body.style.cursor='wait';
							Gp.Services.getConfig({
								apiKey: '<?php echo(CLE_GEOPORTAIL); ?>',
								callbackSuffix: '',
								onSuccess: function (response) {
									// votre utilisation de l'extension Géoportail pour Leaflet
									go()
								}
							});
					}

				}
				else { // AUTOCONF présent
					window.onload = function () {
							document.body.style.cursor='wait';
							Gp.Services.getConfig({
								serverUrl: 'inc/autoconf.json',
								callbackSuffix: '',
								onSuccess: function (response) {
									// votre utilisation de l'extension Géoportail pour Leaflet
									go()
								}
							});
					}
				}


			</script>
	
		
		<script type="text/javascript">
		<!--
		//gestion des commandes déclenchées par ALT + une touche
		
		// cacher ou montrer tout ce qui n'est pas carte ou trace: ALT+x
		// cacher ou montrer l'aide': ALT+a
		var alt = false;
		numCartes = false; // tableau d'assemblage non affiché

		if(document.addEventListener){ //code for Moz
			document.addEventListener("keydown",keyCapt,false);
			document.addEventListener("keyup",keyCapt,false);
			document.addEventListener("keypress",keyCapt,false);
		}
		else{
			document.attachEvent("onkeydown",keyCapt); //code for IE
			document.attachEvent("onkeyup",keyCapt);
			document.attachEvent("onkeypress",keyCapt);
		}
		
		function keyCapt(e){
			if(typeof window.event!="undefined"){
				e=window.event;//code for IE
			}
			if(e.type=="keydown"){
				if (e.keyCode==18) {
					alt = true;
				}
			}
			else if(e.type=="keyup"){
				if (e.keyCode==18) {
					alt = false;
				}
			}

			if (alt && e.keyCode==65) { // A aide
				alt = false;
				if (document.getElementById('aide').style.display=='inline') document.getElementById('aide').style.display = 'none';
				else document.getElementById('aide').style.display = 'inline';
			}

			if (alt && e.keyCode==88) { // ALT+x: tout cacher tout montrer
				alt = false;
				if (document.getElementsByClassName('leaflet-control-container')[0].style.display=='inline')
					document.getElementsByClassName('leaflet-control-container')[0].style.display='none';
				else
					document.getElementsByClassName('leaflet-control-container')[0].style.display='inline'
			}
			
			if (alt && e.keyCode==80) { // ALT=p mémorise par cookie la position du centre de la page
				var latCentrePage = map.getCenter().lat;
				var lonCentrePage = map.getCenter().lng;
				createCookie('lat',latCentrePage,30); //30 jours
				createCookie('lon',lonCentrePage,30); //30 jours
				
				alert("Les coordonnées du centre de la page a été enregistrées dans 2 cookies : latitude : "+latCentrePage+" ; longitude : "+lonCentrePage);
				
			}
			
			if (alt && e.keyCode==82) { //ALT R afficher la résolution d'impression
				alt = false;
/*
				// source: https://www.masinamichele.it/2018/05/04/gis-the-math-to-convert-from-epsg3857-to-wgs-84/
				var latMetrique = map.getCenter().y;
				var  b = 20037508.34;
				var latDeg = Math.atan(Math.exp(latMetrique * Math.PI / b)) * 360 / Math.PI - 90;
*/			
				var latDeg = map.getCenter().lat;
				var latRad = latDeg*Math.PI/180;
				var res = Math.round(132.77/Math.cos(latRad)*10)/10;
				alert("Après la capture de la page, choisir comme résolution d'impression: "+res+" pixels par pouce");
			}
<?php
	if ($fichierGpxCheminWeb!='') {	
?>
				
				if (alt && (e.keyCode==48 ||e.keyCode==96 )) { // 0 rangée supérieure ou pavé numérique afficher/cacher les nb de km
					alt = false;
					layersSymbolGroup.eachLayer(function (layer) {
						layer.toggleTooltip();
					});

				}
				
<?php
		if ($existeWpt) {	
?>
				
				if (alt && e.keyCode==84) { // T afficher/cacher les noms des points de passage
					alt = false;
					layersWptGroup.eachLayer(function (layer) {
						layer.toggleTooltip();
					});
				}
<?php
		}	
?>
<?php
	}	
?>

				
			if (alt && e.keyCode==73) { // I afficher/cacher les numéros de carte
				alt = false;
				if (numCartes) {
					lyrNumIgn.removeFrom(map);
					numCartes = false;
				}
				else {
					lyrNumIgn.addTo(map);
					numCartes = true;
				}
			}
		}

		-->
		</script>

	</body>
</html>
