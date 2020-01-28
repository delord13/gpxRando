<?php
////////////////////////////////////////////////////////////////////////////////
//                            common.inc.php
//
//                            application gpxRando
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// numéro de version $GLOBALS['numeroVersion']
////////////////////////////////////////////////////////////////////////////////

// en production : les erreurs ne sont pas affichées
ini_set("display_errors", '0');

$numeroVersion = "4.07"; // 28/01/2020 (v1.7: 21/07/2012)
/*
3.01 : 17/01/2015
	- carte : trace accompagnée de flèches rouges indiquant le ses ; 1 flèche par km ; flèche à 1 " d'arc à droite du point de référence (point rouge sur la trace); point de départ signalé par un point rouge sans flèche
	- tdm : ajout automatique d'un point de départ et d'un point d'arrivée si le fichier ne contient pas de waypoint
	- éditeur : l'altitude des points nouveaux est ajoutée quelque soit l'option sur les points existants
3.02 : 21/01/2015
	- accès à toutes les cartes IGN disponibles : FXX -> WLD ; pb pour Nouvelle Calédonie
	- utilisation du SRTM1 maintenant disponible ; MNT à 1" d'arc 30 m en latitude ; 22 à 40 m en longitude pour FXX
	- latitude UTM hémisphère Sud : correction des étiquettes des lignes
	- tdm : correction des bandes UTM pour l'hémisphère Sud
3.03 : 17/02/2015
	- correction des calculs d'azimut des points de passage
	- recalcul des azimuts au chargement du tdm pour corriger les anciens azimuts faux
	- gpxRandoEditeur  et visualiserGpx : div # aide draggable et resizable (js/jquery : contient jquery-uid et jaquery)
3.04 :
	-gpx2tdm
		- tableau de marche : 
			réduction de la taille du champ animateur
			ajout d'un champ téléphone
			si création à partir d'un gpx = au chargement, affichage du choix date
		- fiche rando :
			animateur : contenu par défaut = animateur + " tél " téléphone si champ tél non vide
		- enregistrer tdm : champ animateur = animateur + " tél " téléphone si champ tél non vide
3.05 : 28/03/2015
	- index.php
		suppression de l'option : altitudes manquantes
		alert si choix de réduire : temps de calcul et arrêt possible avant réduction complète
	-  gpxRandoEditeur.php
		 - nouvel algorithme de réduction de trace : prise en compte de la distance avant et après et de la réduction de la longueur de la trace ; arrêt au bout de 20 secondes ; messages sur le résultat.
		 - altitudes : calcul automatique des altitudes manquantes (nouveaux trkpt et manquants)
		TODO : 
	- gpx2tdm utilise aussi jquery-uid pour datepicker : il faudrait unifier tout le bordel
	- gpxRandoEditeur : si seult wpt dans gpx : activer l'outil créer une trace
3.06 : 20/08/2015
	- visualiser.php : prise en compte de la variation de l'échelle selon la latitude : tableau pour la résolution d'impression en fonction de la latitude UTM; contribution de Jean Guilo
3.07 : 01/09/2015
	- visualiser.php : affichage de la résolution d'impression en fonction de la latitude par clic sur la carte
3.08 : 02/09/2015
	- visualiser.php : affichage de la résolution d'impression en fonction de la latitude par ALT+R
3.09 : 18/09/2015 : gpxRandoEditeur.php : outil de mesure d'altitude ; modification de l'aide
3.10 : 28/09/2015 : gpxRandoEditeur : possibilité d'ouvrir un gpx et de créer un gpx (2 couches) et modification de la page index en conséquence; message de non-réduction du nombre de points de trace.
3.11 : 04/10/2015 : 
- gpx2tdm : ajout d'un bouton "Analyser" qui affiche une page html d'analyse de la trace : : distances, dénivelées et pentes entre les points de trace
- visualiser.php : ajout de la couche n° de carte IGN
3.12 : 19/01/2016
- visualiser.php : ajout de l'outil Palette pour mettre en forme la trace et refonte de l'aide
3.13 : 14/03/2017
- correction du bug de l'affectation des altitudes après édititon de la trace
- suppression de l'encadré des remarques vides dans la fiche-rando
- suppression des 2 points si le titre des compléments est vide dans la fiche-rando
3.14 ; 13/09/2017 1.7
 - js et css ne sont plus téléchargés sur Géoportail : copiés en local
 - accepte les gpx issus de CartoExploreur avec 'xml version="1.1"' transformé en 'xml version="1.0"'
3.15 18/10/2017
 - évite la division par 0 dans le calcul de l'inverse d'une distance en remplaçant 0 par 0.00001 dans la fonction distance
3.16 22/10/2017
 - correction de la récupération des altitudes des points d'une trace chargée
 - récupération des 7 décim07/12/201ales des coordonnées géographiques des points d'une trace chargée
 - effacement des variables de session au début de index.php
 3.17 26/10/2017
 - nouvelle correction de la récupération des altitudes et des 7 décimales descoordonnées des points d'une trace chargée : 
	- altitudes arrondies en entier
	- indexation des variables de session sur 5 décimalesdatePi
3.18 30/10/2017
	- gpxRandoEditeur.php : séparation des codes js et php : pas de php à l'intérieur des scripts js sauf pour le passage de valeurs
	- reste à faire : même chose pour visualiser.php
3.19 02/12/2017
   - index/php : affichage du logo IRLPT sur hébergement lautre.net
   - gpx2tdm.php : fiche-rando : champs date et animateur = ceux du TDM ; 
      recalcul avant l'affichage du profil et de la fiche-rando
   - gpx2tdm.php : "avec noms" coché par défaut sur hébergement free.fr (demande de Jean-Claude Marie)
   - gpx2tdm.php : sur hébergement lautre.net :
      animateur1 et animateurs2 : liste déroulante ; niveau : liste déroulante
   - gpx2tdm.php et visualiser.php : modification des outils de capture d'écran : 
      Firefox : Page Saver WE; Chrome : Screenshot Extension
3.20 31/12/2017
	- ajout du préfixe ED_ à toutes les variables de session de gpxRandoEditeur pour éviter les confusions avec gpx2tdm
3.21 04/03/2018 : modification de la version "CLUB"
3.22 20/04/2018 : ajout de l'export du TDM en csv
3.23 28/05/2018 
	- correction de la récupération des altitudes des points d'une trace chargée ; conservation des décimales ; correction du bug dû aux arrondis des coordonnées de l'API
	- récupération de toutes les décimales des coordonnées géographiques des points d'une trace chargée ; - - arrondi des altitudes au mètre
3.24 01/07/2018
   - visualiser.gpx : nom de la rando comme titre de page et de fichier image capturée
3.25 29/11/2018
	- ajout de l'IBP Index
	- adaptation pour Club Pédestre Chabeuillois
	- correction des ele aberrantes dans
		- dans gpxRandoEditeur : remplacer par l'ele non aberrante avant ou après (même si recalcul non demandé)
		- dans gpx2tdm : remplacer par l'ele non aberrante avant ou après (pour corriger les anciens TDM)
3.26 05/12/2018
	- dans gpx2tdm chaque altitude manquante est remplacée par la non-manquante la plus proche
	- ajout des mentions légales pour version Free
3.27 10/12/2018
	- gpx2tdm : au chargement d'un tdm, le nom de rando est remplacé par le nom du fichier tdm (sans extension) => le nom de fichier "normalisé" est proposé par défaut lors de l'enregistrement
	- index.php : suppression de C:\fakepath\ devant le nom du fichier fautif dans le message d'alerte en cas d'erreur de type de fichier
3.28 04/01/2019
	- suppression de l'utilisation de realpath (incompatible Free 5.6)
	- IRPT et CPC : trajetCovoiturage : calculé automatiquement par OpenRoute Service (automatique pour création ; au choix à l'ouverture d'un tdm existant)
	- suppression de l'option aller-retour à la création d'un tdm (erreur de l'algorithme')
3.29 22/03/2019
	- suppression de l'itinéraire routier par openRouteService pour IRLPT
	- suppression du sous-titre pour IRLPT
	- possibilité d'effacer les coordonnées du parking
	- indice IBP renommé en "Cotation Effort FFRandonnée" (sans commentaire) pour IRLPT
	- "Itinéraire routier suggéré" replacé après "Trajet routier en km" pour IRLPT
	- proposition de visualiser le ou les itinéraires auto
3.30 16/06/2019
	- fichier param.inc.php contenant tous les paramètres de l'application modifiables
	- en conséquence, modification des fichiers inc et des scripts php
3.31 22/06/2019
	- nettoyage du code
	- contrôle automatique de la version php
	- altitudes fournies par service Géoportail Alticodage au pas de 5m (à la place de SRTM1)
3.32 28/06/2019
	- affichage de la carte avec trace : correction de l'outil palette : suppression du doublon, correction de la position
	- application publique : changement d'hébergeur : de Free à l'Autre.net ; nom de domaine : gpxrando.fr
	- openRoute Service : calcul d'itinéraire routier :  correction de la distance Aller-Retour
	- ajout d'un avertissement sur l'usage de l'outil "ciseaux" : à n'utiliser que pour supprimer le début et/ ou la fin d'une trace pour éviter d'avoir plusieurs segments de trace dans la mesure où l'API Géoportail renvoie les éventuels segments dans un ordre aléatoire
3.33 05/07/2019
	- choix du MNT pour le calcul des altitudes : Service Alticodage (par défaut) ou SRTM1
3.34 06/08/19
   - suppression du choix du MNT ; service Alticodage imposé
   - abandon du calcul de l'indice IBP après 2 secondes sans réponse
3.35 18/08/19
	- non affichage des erreurs php
4.01 30/10/19
	- Carte : passage à SDK Geoportail v 3 (il manque encore le carroyage UTM)
	- gpxRandoEditeur : remplacé par lien vers visualiserGpx
	- gpx2tdm : ajout de l'option de recalcul des altitudes par service Alticodage de l'IGN'
4.02 07/11//19
	- Carte : ajout des symboles : flèches kilométriques et drapeaux
	- Carte : ajout de l'affichage des noms des points de passage par couche KML
4.03 16/11/19
   - gpx2tdm : lissage moyenne par distance remplace moyenne par point (suggestion de Jean Guillo)
   - gpx2tdm : nouveau menu de commande
4.04 29/11/19
	- utilisation du fichier autoconf.json pour contourner les dysfonctionnements récurrents du service autoconf 
4.05 07/12/19
	- abandon API Géoportail v3 SDK
	- passage à API Géoportail v3 Leaflet
	- Carte : rétablissement de l'affichage du carroyage UTM
	- Carte : impression directe par Leaflet
	- visualiserGpx.php renommé et déplacé : util/visualiserGpx.php => carte.php
	- index.php : util/visualiserGpx.php => carte.php
	- inc/gpx2tdm.inc.php : util/visualiserGpx.php => carte.php
	- inc/config.inc : adresse autoconf modifiée
4.06 01/01/20
	- carte.php : Leaflet + GpPluginLeaflet pour layerSwitcher et mousePosition
	- carte.php : LeafletPlugins : UTM KML
4.07 28/01/20
	- gpx2tdm : OpenRoute Service v2 pour le calcul des itinéraires routiers
	- gpx2tdm : IBPIndex calculé sur le trace avec les altitudes éventuellement modifiées
	- carte : zoom 11 pour les itinéraires routiers
	- index : ajout de la licence et de l'adresse de courriel pour contact'
*/
////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// REFERER
	////////////////////////////////////////////////////////////////////////////////
	if (isset($_SERVER['HTTPS'])) {
		if ($_SERVER['HTTPS']!="") $debut = "https://";
		else $debut = "http://";
	}
	else  $debut = "http://";
	define("REFERER", $debut.$_SERVER['SERVER_NAME']);
	////////////////////////////////////////////////////////////////////////////////

	
	////////////////////////////////////////////////////////////////////////////////
	// version PHP
	////////////////////////////////////////////////////////////////////////////////
	// l'application nécessite une version de php supérieure ou égale à 5.6
	// avec une version au moins égale à 5.1.3 l'application fonctionnera sans 
	// certaines fonctionnalités : IBP Index, OpenRoute Service, enregistrement PDF
	$version = explode('.',PHP_VERSION);
	if ($version[0]>5) { // 7et+
		define("AU_MOINS_56", TRUE); 
	}
	else {
		if ($version[0]==5) {
			if ($version[1]>=6) { // 5.6 et +
				define("AU_MOINS_56", TRUE);
			}
			else {
				if ($version[1]>1) { // 5.1 et plus
					define("AU_MOINS_56", FALSE);
				}
				else {
					if ($version[1]==1 && $version[2]>=3) { // 5.1.3 et plus
						define("AU_MOINS_56", FALSE);
					}
					else die("L'application nécessite une version de php au moins égale à 5.1.3. alors que la version en service sur ce serveur est : ".PHP_VERSION);
				}
			}
		}
	}
	//define("AU_MOINS_56 ", TRUE); // FALSE 5.1.3<=php<5.6 ; TRUE php>=5.6

	//////////////////////////////////////////////////////////////////////////////////
	// nettoyage de $_SESSION['connexion'] Est-ce bien utile ??????
	////////////////////////////////// ////////////////////////////////////////////////
	if ($_SERVER['SERVER_NAME']=="gpx2tdm.free.fr") $_SESSION['connexion'] = array();
	
	//////////////////////////////////////////////////////////////////////////////////
	// Nettoyage du répertoire tmp contenant les gpx modifié par gpxRandoEditeur
	////////////////////////////////// ////////////////////////////////////////////////
	$repertoire = realpath('tmp');
	$fichierTmp = array_diff(scandir($repertoire), array('.', '..', 'index.php'));
	foreach ($fichierTmp AS $unFichier) {
		$chemin = "$repertoire/$unFichier";
		// plus de 5 minutes
		$hier = time()-300;
		if (filemtime($chemin)<$hier) unlink($chemin);
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Contrôle d'accès sur istres.rando.lautre.net Est-ce bien Utile ??????????????????????
	//////////////////////////////////////////////////////////////////////////////////

	if (($_SERVER['SERVER_NAME']=="istresrando.lautre.net" OR $_SERVER['SERVER_NAME']=="istresrando.fr" ) && !isset($_COOKIE['spip_session'])) {
	?>
	<html  lang="fr-fr">
		<head>
			<title>gpxRandoEditeur</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
			<link rel="shortcut icon" type="image/ico" href="images/gpx2tdm.ico" />
		</head>
		<body>
			<p>&nbsp;
			</p>
			<p>&nbsp;
			</p>
			<p>&nbsp;
			</p>
			<p>&nbsp;
			</p>
			<p>&nbsp;
			</p>&nbsp;
			<p>
			</p>&nbsp;
			<p>
				Veuillez vous connecter au site du club en cliquant sur : <a href="/spip/index.php" target="_blank">istresrando.fr</a>
			</p>
			<p>
				avant de revenir sur cette page et de cliquer <a href="/spip/applis/gpxRando/">ici</a>.
			</p>
		
		</body>
	</html>

	<?php
		die();
	}

// function azimut entre 2 points : formule vérifiée
	function azimut ($pt1, $pt2) {
		$lat1 = $pt1['lat'];
		$lon1 = $pt1['lon'];
		$lat2 = $pt2['lat'];
		$lon2 = $pt2['lon'];
		// passage en radians
		$lat1 = M_PI*$lat1/180;
		$lon1 = M_PI*$lon1/180;
		$lat2 = M_PI*$lat2/180;
		$lon2 = M_PI*$lon2/180;

		$x = cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos($lon2-$lon1);
		$y = sin($lon2-$lon1)*cos($lat2);
		$a = atan2($y,$x);
		$azimutDeg = $a*180/M_PI;
		if ($azimutDeg<0) $azimutDeg = 360 + $azimutDeg;
		return $azimutDeg;
		
}


	function real_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
					if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
						$ip = $xip;
						break;
					}
			}
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		return $ip;
	}


	////////////////////////////////////////////////////////////////////////////////
	// détermination du nbre de car du codeLettre du nom d'un point de passage
	////////////////////////////////////////////////////////////////////////////////
	function nbCarCodeLettre($ch) { // lettre maj [chiffre] : espace
			$codeNum[0] = ord(substr($ch,0,1));
			$codeNum[1] = ord(substr($ch,1,1));
			$codeNum[2] = ord(substr($ch,2,1));
			$codeNum[3] = ord(substr($ch,3,1));
			if ($codeNum[0]>64 and $codeNum[0]<91) { // lettre maj
				if ($codeNum[1]==58 and $codeNum[2]==32) { // : espace
					return 3;
				}
				if ($codeNum[1]>47 and $codeNum[1]<58) { // chiffre
					if ($codeNum[2]==58 and $codeNum[3]==32) { // : espace
						return 4;
					}
					else return 0;
				}
				else return 0;
			}
			else return 0;
	}
	////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
// pour les serveurs où magic_quotes_gpc = On et afin d'éviter les stripslashes()
////////////////////////////////////////////////////////////////////////////////
if (get_magic_quotes_gpc()) {
    function undoMagicQuotes($array, $topLevel=true) {
        $newArray = array();
        foreach($array as $key => $value) {
            if (!$topLevel) {
                $key = stripslashes($key);
            }
            if (is_array($value)) {
                $newArray[$key] = undoMagicQuotes($value, false);
            }
            else {
                $newArray[$key] = stripslashes($value);
            }
        }
        return $newArray;
    }
	$_GET = undoMagicQuotes($_GET);
	$_POST = undoMagicQuotes($_POST);
	$_COOKIE = undoMagicQuotes($_COOKIE);
	$_REQUEST = undoMagicQuotes($_REQUEST);
}
////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
// fonctions de calcul de distance
//////////////////////////////////////////////////////////////////////////////////////////////////////
	// fonction distance entre 2 points
	function distance ($lat1, $lon1, $lat2, $lon2) {
		// rayon moyen de la terre en m
		$r = 6367445;
		// passage en radians
		$lat1 = M_PI*$lat1/180;
		$lon1 = M_PI*$lon1/180;
		$lat2 = M_PI*$lat2/180;
		$lon2 = M_PI*$lon2/180;
		$d = abs($r*acos(sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2)*cos($lon1-$lon2)));
		if (is_nan($d)) $d = 0;
// pour éviter une division par 0 pour le calcul de l'inverse de la distance'
		if ($d==0) $d=0.00001;
		return $d;
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////
// fonctions de calcul des ele 1"
//////////////////////////////////////////////////////////////////////////////////////////////////////

	function ele3LatLon($fp,$lat,$lon) { // INUTILISÉ SRTM1 lat et lon = secondes entières et de la longitude ;$lon est la longitude divisée par l'intervalle en longitude 
		fseek($fp, ($lon*7214+3428)+$lat*2+8 ); // 		fseek($fp, ($lat*7214+3428)+$lon*2 );

		//((1200-$lat)*1201+$lon)*2);
		$data = fread($fp,2);
		$ele = unpack("n", $data); // non signe big endian
		$ele[1] = (integer) $ele[1];
		if ($ele[1]>32767) $ele[1] = (65535-$ele[1])*-1; // signé
		
		return $ele[1];
	}

	// elevation d'un point lat et lon en ° décimaux
//	function eleLatLon($lat,$lon) { // INUTILISÉ SRTM1
	function eleLatLon($ptCoord) { // INUTILISÉ SRTM1
		$ele = array();
		foreach ($ptCoord AS $j => $coord) {
			$lat = $coord['lat'];
			$lon = $coord['lon'];
			// latSec
			$latSec = ($lat-floor($lat))*3600;
			// lonSec
			$lonSec = ($lon-floor($lon))*3600;
			// latDeg
			$latDeg =  floor($lat); //(integer)
			// lonDeg
			$lonDeg =  floor($lon); //(integer)
			
			// hémisphères
			if ($lat<0) $NS = "s";
			else $NS = "n";
			if ($lon<0) $EW = "w";
			else $EW = "e";

			// fichier dted2 à ouvrir
			$ficEle = SRTM1_URL.$NS.sprintf("%02d",abs($latDeg));
			$ficEle .= "_".$EW.sprintf("%03d",abs($lonDeg));
			$ficEle .= "_1arc_v3.dt2";
			if (!($fp = fopen($ficEle,"rb"))) return(""); // si pas de fichier d'ele

			// prise en compte des degrés de précision différents en longitude en fonction de la latitude
			$pos = abs($latDeg);
			if ($pos<50) $n = 1;
			else 
				if ($pos>=50 && $pos<75) $n = 2;
				else 
					if ($pos>=75 && $pos<80) $n = 4;
					else $n = 6;
				
			// calcul des min3 et max3
			$minLatSec = (integer) floor($latSec);
			$maxLatSec = $minLatSec+1;

			$minLonSec = (integer) floor($lonSec/$n);
			$maxLonSec = $minLonSec+1;

			// NW maxLat maxLon
			$ele[0] = ele3LatLon($fp,$maxLatSec,$maxLonSec);
			$coef[0] = 1/(distance($lat,$lon,$latDeg+$maxLatSec*1/3600,$lonDeg+$maxLonSec*($n)/3600));
			//SW maxLat minLon;
			$ele[1] = ele3LatLon($fp,$maxLatSec,$minLonSec);
			$coef[1] = 1/(distance($lat,$lon,$latDeg+$maxLatSec*1/3600,$lonDeg+$minLonSec*($n)/3600));
			// NE minLat maxLon
			$ele[2] = ele3LatLon($fp,$minLatSec,$maxLonSec);
			$coef[2] = 1/(distance($lat,$lon,$latDeg+$minLatSec*1/3600,$lonDeg+$maxLonSec*($n)/3600));
			//SE minLat minLon
			$ele[3] = ele3LatLon($fp,$minLatSec,$minLonSec);
			$coef[3] = 1/(distance($lat,$lon,$latDeg+$minLatSec*1/3600,$lonDeg+$minLonSec*($n)/3600));
			// fermeture du fichier
			fclose($fp);

			// moyenne pondérée
			$cumulEle = 0;
			$cumulCoef = 0;
			foreach ($ele AS $i => $unEle) {
				if ($unEle>-1000) {//-32766
					$cumulEle += $unEle*$coef[$i];
					$cumulCoef += $coef[$i];
				}
			}

			if ($cumulCoef!=0)
				$res = round($cumulEle/$cumulCoef);
			else
				$res = "";
				
			// correction des ele aberrantes	
			if ($res>5000 || $res<-1000)  $res="";
			$ele[$j] = $res;
//			return $res;
		} //pour chaque point de trace
		return $ele;
	}
// fin des fonctions de calcul des ele

//////////////////////////////////////////////////////////////////////////////////////////////////////
	function eleGeoportail5000($lon,$lat,$ele,$premier) {
		// complète l'array $ele en ajoutant au rang $premier
		// suppression du dernier |
		$lon = substr($lon,0,-1);
		$lat = substr($lat,0,-1);
		$param = $lon.$lat;
		$opt = "https://wxs.ign.fr/".CLE_GEOPORTAIL."/alti/rest/elevation.json?$param&zonly=true";

		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL,$opt);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_REFERER, REFERER);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Accept: application/json; charset=utf-8"
		));
		$reponse = curl_exec($ch);
		curl_close($ch);
//echo REFERER;
//var_dump($reponse); die();
		$tab = json_decode($reponse,TRUE);	
		foreach ($tab as $elevations) {
			$i = $premier;
			foreach ($elevations as $uneEle) {
				$ele[$i] = round($uneEle);
				$i++;
			}
		}
		return $ele;
	} // fin eleGeoportail5000

	function eleGeoportail($ptCoord) { // rajoute les altitudes à tous les points trkpt d'une trace en utilisant le service Alticodage de Géoportail ; MAX 5000 points maintenant 100 !!
	// $ptCoord : array lon et lat
	// $ele : array 
		$ele = array();
		$premier = 0;
		$i = 0;
		$n = 0;
		$lon = "lon=";
		$lat = "&lat=";
		foreach ($ptCoord AS $unPtCoord) {
			$lon .= $unPtCoord['lon'].'|';
			$lat .= $unPtCoord['lat'].'|';
			$n++;
			$i++; // rang du prochain
			if ($i==100) { //malgré ce que dit la doc Alticodage il ne faut pas dépasser 100
				$ele = eleGeoportail5000($lon,$lat,$ele,$premier);
				$i = 0;
				$premier = $n;
				$lon = "lon=";
				$lat = "&lat=";
			}
		}
		if ($i>0) $ele = eleGeoportail5000($lon,$lat,$ele,$premier);
		return $ele;
	} // fin eleGeoportail

//////////////////////////////////////////////////////////////////////////////////////////////////////////// correction des ele manquantes ou vides dans une chaîne Gpx
	function corrigeEleGpx($chGpx) {		// INUTILISÉ
		$gpx = simplexml_load_string($chGpx);
		
		$idTrk = -1;
		foreach ($gpx->trk as $unTrk) {
			$idTrk++;
			foreach ($unTrk->trkseg as $untrkseg) {
				$idTrkpt = -1;
			
				foreach($untrkseg->trkpt as $unTrkpt) {
					$idTrkpt++;
					if (!isset($unTrkpt->ele) || $unTrkpt->ele=="" || $unTrkpt->ele<-1000 || $unTrkpt->ele>9000) {
						$gpx->trk[$idTrk]->trkpt[$idTrkpt]->ele = "";
					}
				}
			}
		}
		return $gpx->asXML();
	}
		
// fin correction des ele manquantes ou vides dans une chaîne Gpx
//////////////////////////////////////////////////////////////////////////////////////////////////////

// date
// fonction internationaliserDate
	function internationaliserDate($laDate) {
		$tabDate = explode('/',$laDate);
		return ($tabDate[2]."-".$tabDate[1]."-".$tabDate[0]);
	}

// fonction nationaliserDate
	function nationaliserDate($laDate) {
		$tabDate = explode('-',$laDate);
		return ($tabDate[2]."/".$tabDate[1]."/".$tabDate[0]);
	}
	
// fonction jourDate
	function jourDateFr($laDate) {
		if ($_SERVER['SERVER_NAME']=="localhost") setlocale(LC_TIME, "fr_FR.utf8");
		else setlocale(LC_TIME, "fr_FR.utf8");
		$timeStamp = strtotime($laDate);
		$laDateFr = strftime("%A %e %B %G ",$timeStamp );
		return($laDateFr);
	}
	
// fonction internationaliserHeure
	function internationaliserHeure($lHeure) {
		$tabHeure = explode('h',$lHeure);
		return ($tabHeure[0].":".$tabHeure[1].":00");
	}
	
// fonction nationaliserHeure
	function nationaliserHeure($lHeure) {
		$tabHeure = explode(':',$lHeure);
		return ($tabHeure[0]."h".$tabHeure[1]);
	}

	
// fonction internationaliserDecimal
	function internationaliserDecimal($leDecimal) {
		$leDecimal = str_replace(" ","",$leDecimal);
		return(str_replace(",",".",$leDecimal));
	}
	
// fonction nationaliserDecimal
	function nationaliserDecimal($leDecimal) {
		if (!strpos($leDecimal,".")) $leDecimal .= ",00";
		else $leDecimal = str_replace(" ","",$leDecimal);
		return(str_replace(".",",",$leDecimal));
	}


///////////////////////////////////////////////////////////////////////////////////////////////////////
//enregistrement d'un log dans le fichier /log/log.csv
//////////////////////////////////////////////////////////////////////////////////////////////////////
	function enregistrerLog ($appli, $evenement, $fichier) {
		if (LOG_RECORD) { // seulement pour Chabeuil
			// structure :
			// dateheure; nomUtilisateur; appli ; evenement; fichier
			$dateHeure = date("Y-m-d H:i:s");
			$utilisateur = $_SESSION['connexion']['nomUtilisateur'];
			if (isset($_SESSION['connexion']['prenomUtilisateur'])) $utilisateur = $_SESSION['connexion']['prenomUtilisateur']." ".$utilisateur;
			$realIp = real_ip();
			$ligne = $dateHeure.";".$utilisateur.";".$appli.";".$evenement.";".$fichier.";".$realIp."\n";
			$scriptNameRaccourci = substr($_SERVER['SCRIPT_NAME'],0,-1); // supprime l'éventuel / final
			$n = substr_count($scriptNameRaccourci,'/');
			$chemin = '';
			for ($i = 1; $i < $n; $i++) $chemin .= '../';
			file_put_contents($chemin."log/log.csv", $ligne, FILE_APPEND | LOCK_EX);
		}
	}
//////////////////////////////////////////////////////////////////////////////////////////////////////

?>
