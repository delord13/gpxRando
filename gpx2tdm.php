<?php
////////////////////////////////////////////////////////////////////////////////
//                            gpx2tdm.php
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
require 'inc/sessionsMultiplesAppli.inc.php';
require 'inc/config.inc.php';
require 'inc/common.inc.php';
require 'inc/gpx2tdm.inc.php';

//error_reporting(E_ALL);
//mini_set("display_errors", 1);

//var_dump($_POST); die();
////////////////////////////////////////////////////////////////////////////////
 // Main : action selon POST
////////////////////////////////////////////////////////////////////////////////
/*
 * var_dump($_SESSION);
var_dump($_POST);

*/
{ // Main : action selon POST


// on agit suivant le POST newAction
if (isset($_POST['newAction'])) {


	if ($_POST['newAction'] == "creer") {
		initialiserTdm($_POST['mode']); 	// standard ou test
		calculerTdm();
		// log
		enregistrerLog ('gpx2tdm', 'création d\'un tdm', $_FILES['fichierGpx']['name']);

		afficherTdm("rien");
	}

	if ($_POST['newAction'] == "charger") {

		// log
		if ($_POST['mode']=="bdTdm") {
			$nomFichierTdm = $_POST['nomFichierTdm'];
			$evenement = 'édition d\'un tdm depuis bdTdm';
		}
		else {
			$nomFichierTdm = $_FILES['fichierTdm']['name'];
			$evenement = 'édition d\'un tdm depuis gpxRando';
		}
		enregistrerLog ('gpx2tdm', $evenement, $nomFichierTdm);
      chargerTdm($_POST['mode']); // standard ou test ou bdTdm
		calculerTdm();//$_FILES['fichierGpx']['name']
		afficherTdm("rien");
	}

	if ($_POST['newAction'] == "visualiser") {
		mettreAJourSession();
		calculerTdm();
//		enregistrerFichier($xml);
      switch($_POST['modeAgir']) {
         case 'auto' :
         afficherTdm("afficherCarteAuto");
         break;
         case 'portrait' :
			afficherTdm("afficherCartePortrait");
         break;
         case 'paysage' :
         afficherTdm("afficherCartePaysage");
         break;
      }
   }

   if ($_POST['newAction'] == "enregistrer") {
		mettreAJourSession();
		calculerTdm();
		// log
		enregistrerLog ('gpx2tdm', 'enregistrement d\'un tdm', $_SESSION['nomRando'].".tdm");
		afficherTdm("envoyerTdm");
	}

	if ($_POST['newAction'] == "envoyerTdm") {
		$xml = construireTdm();
		//enregistrerLog ('gpx2tdm', 'enregistrement d\'un tdm', $_SESSION['nomRando'].".tdm");
		envoyerTdm($xml);
	}

	if ($_POST['newAction'] == "envoyerTdmCSV") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'envoi d\'un tdm (csv)', $_SESSION['nomRando'].".tdm");
		$csv = construireTdmCSV();
		envoyerTdmCSV($csv);
	}
	

	if ($_POST['newAction'] == "imprimerTdm") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'impression d\'un tdm', $_SESSION['nomRando'].".tdm");
//		imprimerTdm();
		afficherTdm("imprimerTdm");
	}

	if ($_POST['newAction'] == "imprimeFicheHtml") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'impression d\'une fiche rando (html)', $_SESSION['nomRando'].".tdm");
//		enregistrerFichier($xml);
		afficherTdm("imprimeFicheHtml");
	}

	if ($_POST['newAction'] == "imprimeFichePdf") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'impression d\'une fiche rando (pdf)', $_SESSION['nomRando'].".tdm");
//		enregistrerFichier($xml);
		afficherTdm("imprimeFichePdf");
	}

	if ($_POST['newAction'] == "creerTrk") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'création d\'un gpx (_wpt_trk)', $_SESSION['nomRando'].".tdm");
		afficherTdm("envoyerTrk");
	}

	if ($_POST['newAction'] == "envoyerTrk") {
		$xml = construireTrk();
		envoyerTrk($xml);
	}

	if ($_POST['newAction'] == "creerTrkLisse") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'création d\'un gpx (_wpt_trk)', $_SESSION['nomRando'].".tdm");
		afficherTdm("envoyerTrkLisse");
	}

	if ($_POST['newAction'] == "envoyerTrkLisse") {
		$xml = construireTrkLisse();
		envoyerTrkLisse($xml);
	}

	if ($_POST['newAction'] == "creerWpt") {
		mettreAJourSession();
		calculerTdm();
		//enregistrerLog ('gpx2tdm', 'création d\'un gpx (_wpt)', $_SESSION['nomRando'].".tdm");
		afficherTdm("envoyerWpt");
	}

	if ($_POST['newAction'] == "envoyerWpt") {
		$xml = construireWpt();
		envoyerWpt($xml);
	}

	if ($_POST['newAction'] == "analyser") {
		$xml = construireAnalyse();
		enregistrerLog ('gpx2tdm', 'analyse d\'une trace', $_SESSION['nomRando'].".tdm");
		envoyerAnalyse($xml);
	}
	
	if ($_POST['newAction'] == "recalculer") {
//var_dump($_SESSION); die();		
		mettreAJourSession();
		calculerTdm();
		afficherTdm("rien");
	}

//var_dump($_SESSION); die();
}
else {
	// cas de l'appel initial de l'application
	header('Location: ../index.php');
}

// fin Main : action selon POST
////////////////////////////////////////////////////////////////////////////////
}

	////////////////////////////////////////////////////////////////////////////////
	// initialiser à partir du fichier gpx
	function initialiserTdm($mode) {
		
		// fonctions de correction des noms de communes et de rando à partir du nom de fichier sans extension découpé selon le trait d'union
	
		function corrigerCommune($commune) {
			// espace avant majuscule
			$pattern = '/([A-Z])/';
			$replacement = ' $1';
			$ch = trim(preg_replace($pattern, $replacement, $commune));
			$commune = $ch;
			// trait d'union à la place d'espace
			$ch = trim(preg_replace('/ /', '-', $commune));
			// sauf après Le La Les initiaux
			$ch = trim(preg_replace('/^Les-/', 'Les ', $ch));
			$ch = trim(preg_replace('/^Le-/', 'Le ', $ch));
			$ch = trim(preg_replace('/^La-/', 'La ', $ch));
			$ch = trim(preg_replace('/D-/', "D'", $ch));
			$ch = trim(preg_replace('/L-/', "L'", $ch));
			return($ch);		
		}

		function corrigerRando($rando) {
			// espace avant majuscule
			$pattern = '/([A-Z])/';
			$replacement = ' $1';
			$ch = trim(preg_replace($pattern, $replacement, $rando));

			$pattern = '/([a-zA-Z])([0-9])/';
			$replacement = '$1 $2';
			$ch = preg_replace($pattern, $replacement, $ch);

			$pattern = '/(0-9])([a-zA-Z])/';
			$replacement = '$1 $2';
			$ch = preg_replace($pattern, $replacement, $ch);

			$ch = preg_replace('/D /', "D'", $ch);
			$ch = preg_replace('/L /', "L'", $ch);
			
			$ch = preg_replace('/G R /', "GR ", $ch);
			$ch = preg_replace('/G R P /', "GRP ", $ch);
			$ch = preg_replace('/P R /', "PR ", $ch);

			return($ch);		
		}

		function recalculerAltitude() {
		// détermine les valeurs 'ele' dans $_SESSION['trk'] à l'aide du service alticodadage de l'IGN
			foreach ($_SESSION['trk'] AS $i => $trkpt) {
//var_dump($_SESSION['trk']);die();
				// calcul des altitude avec Alticodage de Geoportail
				$ptCoord[$i]['lat'] = (float) $trkpt['lat'];
				$ptCoord[$i]['lon'] = (float) $trkpt['lon'];
				$i++;
			}
			$ele = eleGeoportail($ptCoord);
//var_dump($ele);die();
			foreach ($ele AS $i => $uneEle) {
				$_SESSION['trk'][$i]['ele'] = (string) $ele[$i];
			}
			// enregistrer la trace avec altitudes corrigées : fichier pour IBP Index
		}
		
		
		// initialiser $_SESSION

		if ($mode=="test") {
			//basename($_SERVER['SCRIPT_FILENAME'])."/util/".$_POST['fichierTest']
			$_SESSION['nomRando'] = substr($_POST['fichierTest'],0,-4);
			$_SESSION['nomGpx'] = $_POST['fichierTest'];
	//		$fichierGpx = "../CeCILL/".$_POST['fichierTest'];
			$fichierGpx = $_POST['fichierTest'];
		}
		else { // standard

			// titre de la fiche par défaut : nom de la rando en "français"
			$fileName = $_FILES['fichierGpx']['name'];
			$posTrait = strpos($fileName,"-");
			$commune = trim(substr($fileName,0,$posTrait));
			$fin = trim(substr($fileName,$posTrait+1));
			// supprimer .gpx
			if (strPos($fin,'.gpx')!=0) $fin = substr($fin,0,strpos($fin,".gpx"));
			// supprimer _wpt_trk
			if (strPos($fin,'_wpt_trk')!=0) $nomSortie = substr($fin,0,strPos($fin,'_wpt_trk'));
			else {
				if (strPos($fin,'_trk')!=0) $nomSortie = substr($fin,0,strPos($fin,'_trk'));
				else {
					if (strPos($fin,'_wpt')!=0) $nomSortie = substr($fin,0,strPos($fin,'_wpt'));
					else $nomSortie = $fin;
				}
			}
			$commune = corrigerCommune($commune);
			$nomSortie = corrigerRando($nomSortie);
			$_SESSION['ficheTitreFiche'] = htmlspecialchars_decode($commune." - ".$nomSortie);

			$_SESSION['nomRando'] = substr($_FILES['fichierGpx']['name'],0,-4);
			$_SESSION['nomGpx'] = $_FILES['fichierGpx']['name'];
			$fichierGpx = $_FILES['fichierGpx']['tmp_name'];
			
			if  ($_FILES['fichierWpt']['size']!=0) {
				$fichierWpt = $_FILES['fichierWpt']['tmp_name'];
			}
		}

		$_SESSION['idAffiche'] = "tdm";
		$_SESSION['profilVitesse'] = "off";
		$_SESSION['calcDeniv'] = CALC_DENIV_DEF;
		
		$_SESSION['date'] = date('d/m/Y',time());
		$_SESSION['animateur'] = "";
		$_SESSION['vitesse'] = VITESSE;
		$_SESSION['methode'] = METHODE_DEF;
		$_SESSION['coefPos'] = COEF_POS;
		$_SESSION['coefNeg'] = COEF_NEG;
		$_SESSION['kPos'] = K_POS;
		$_SESSION['kNeg'] = K_NEG;

		// fiche-rando
		$_SESSION['ficheLogoAucun'] = FALSE;
		// encodage de l'image par défaut
		$file = fopen(LOGO_URL,'rb');
		$data = fread($file,filesize(LOGO_URL));
		fclose($file);
		$_SESSION['ficheLogoImage'] = chunk_split(base64_encode($data));
		// type de l'image
		$_SESSION['ficheLogoImageType'] = substr(LOGO_URL,strrpos(LOGO_URL,".")+1);

		if ($_SESSION['date']!="//") {
			$date = $_SESSION['date'];
			$tab_date = explode("/",$date);
			$jour = (int) $tab_date[0];
			$mois = (int) $tab_date[1];
			$an = (int) $tab_date[2];
			setlocale(LC_TIME, 'fr_FR.UTF8');
			$_SESSION['ficheDate'] = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
		}
		else {
			$_SESSION['ficheDate'] = "";
		}

		$_SESSION['ficheLogoUrl'] = LOGO_URL;
		$_SESSION['ficheTitreFiche'] = $_SESSION['ficheTitreFiche'];
		$_SESSION['ficheSousTitre'] = "";
		$_SESSION['fichePresentation'] = "";
		$_SESSION['ficheNiveau'] = "";

//		if (IBP) $_SESSION['ficheIbpIndex'] = $ibpIndex;
		


		
		// spécifique CPC		
		if (NIVEAU_PAR_IBP) {
         if (isset($_SESSION['ficheIbpIndex'])) {
            if ($_SESSION['ficheIbpIndex']<=50) $_SESSION['ficheNiveau'] = "F";
            else
               if ($_SESSION['ficheIbpIndex']<=75) $_SESSION['ficheNiveau'] = "M";
               else 
                  if ($_SESSION['ficheIbpIndex']<=100) $_SESSION['ficheNiveau'] = "M+";
                  else $_SESSION['ficheNiveau'] = "D";
         }
         else {
            $_SESSION['ficheNiveau'] = "";
         }
      }
      if (THEME) {
         $_SESSION['ficheThemeCode'] = "";
         $_SESSION['ficheThemeDescription'] = "";
         $_SESSION['ficheNbParticipants'] = "";
     }
		
		$_SESSION['ficheDenivPosFiche'] = "";
		$_SESSION['ficheDenivNegFiche'] = "";
		$_SESSION['ficheLongueurFiche'] = "";
		$_SESSION['ficheDureeFiche'] = "";
		$_SESSION['ficheDifficultes'] = DIFFICULTES;
		$_SESSION['ficheCarte'] = CARTE;
		$_SESSION['ficheRDV'] = RDV;
		$_SESSION['ficheDepart'] = DEPART;
		$_SESSION['ficheTrajet'] = TRAJET;

		$_SESSION['ficheItiXmlSans'] = "";
		$_SESSION['ficheItiXmlAvec'] = "";
		$_SESSION['ficheItiModeVisualiserSans'] = "";
		$_SESSION['ficheItiModeVisualiserAvec'] = "";
		$_SESSION['ficheItiTitrePageSans'] = "";
		$_SESSION['ficheItiTitrePageAvec'] = "";
		
		$_SESSION['ficheParking'] = "";
		$_SESSION['ficheTrajetKm'] = "";
		$_SESSION['ficheCovoiturage'] = "";
		
		$_SESSION['ficheLaRandonnee'] = "";
		$_SESSION['ficheEquipement'] = EQUIPEMENT;
		$_SESSION['ficheAnimateur'] = htmlspecialchars_decode($_SESSION['animateur']);
		$_SESSION['ficheComplementsTitre'] = "";
		$_SESSION['ficheComplements'] = "";

		// traitement de la trace trk
		$fp = fopen($fichierGpx, "rb");
		clearstatcache();
		$chaineGpx = fread($fp, filesize($fichierGpx));
		fclose($fp);
		
		// correction XML v1.1 : remplace xml version="1.1" par xml version="1.0"
		$chaineGpx = str_replace('xml version="1.1"', 'xml version="1.0"', $chaineGpx);
		$chaineGpx = str_replace("xml version='1.1'", "xml version='1.0'", $chaineGpx);

		$message = "";
		$gpx = simplexml_load_string($chaineGpx);

		if ($gpx===false) $message = "Le fichier n'est pas un fichier gpx valide.";
		if ($message<>"") {
			$message .= " Le tableau de marche ne peut pas être construit.";
			alerterEtRetour($message);
		}

	// contrôle de l'existence d'un seul trk
		$nbTrk = 0;
		foreach($gpx->trk as $trk) {$nbTrk++;}
		if ($nbTrk==0) { $message = "Le fichier gpx ne contient aucun trace valide."; }
		if ($nbTrk>1) { $message = " Le fichier gpx contient plus d'une trace.";}

		// construction de $_SESSION['trk']
		// s'il y a plusieurs trkseg, on les raccorde en 1 seule trace
		$i = 0;
		foreach ($gpx->trk->trkseg as $unTrkseg) {
			foreach($unTrkseg->trkpt as $trkpt) {
				$_SESSION['trk'][$i]['lat'] = (float) $trkpt['lat'];
				$_SESSION['trk'][$i]['lon'] = (float) $trkpt['lon'];
				// ajout des altitudes manquantes
				if (isset($trkpt->ele) && $trkpt->ele!="NaN" && $trkpt->ele!="" && $trkpt->ele!=-32768)
					$_SESSION['trk'][$i]['ele'] = (integer) $trkpt->ele;
				else {
//					$ele = eleLatLon((float) $trkpt['lat'], (float) $trkpt['lon']);
//					$_SESSION['trk'][$i]['ele'] = $ele;
					$_SESSION['trk'][$i]['ele'] = "";
				}
				$i++;
			}
		}
//var_dump($_SESSION['trk']); echo("\n<br>******************************************\n<br>");	
//die();
		// si demandé : recalcul des altitudes par service altimétrique de l'IGN
		// modifie $_SESSION['trk']
		if (isset($_POST['recalculerAltitude'])) recalculerAltitude();
		
		// remplacer chaque ele=="" par ele la  + proche !=""
		$elePrec = "";
		foreach ($_SESSION['trk'] as $i => $trkpt) {
			if (is_numeric($trkpt['ele'])) $elePrec = $trkpt['ele'];
			else {
				if (is_numeric($elePrec)) $_SESSION['trk'][$i]['ele'] = $elePrec;
				else {
					$j = $i+1;
					if (isset($_SESSION['trk'][$j])) {
						while ($_SESSION['trk'][$j]['ele']=="") {
							$j++;
							if (!isset($_SESSION['trk'][$j])) die("La trace ne contient pas d'attribut ele, elle est donc inutilisable.");
						}
						$_SESSION['trk'][$i]['ele'] = $_SESSION['trk'][$j]['ele'];
					}
					else die("La trace ne contient pas d'attribut ele, elle est donc inutilisable.");
				}
			}
		}

		

		// calcul des coordonnées du parking = premier point de trace
		$_SESSION['ficheParking'] = "coordonnées du parking pour GPS : ";
		$lonDec = $_SESSION['trk'][0]['lon'];
		$latDec = $_SESSION['trk'][0]['lat'];
		$_SESSION['ficheParking'] .= $lonDec."° E , ".$latDec."° N ou ";

		$lonSexDeg = (int)($lonDec);
		$lonSexMinDec = ($lonDec-$lonSexDeg)*60;
		$lonSexMin = (int)($lonSexMinDec);
		$lonSexSec = round(($lonSexMinDec-$lonSexMin)*60);
		$_SESSION['ficheParking'] .= $lonSexDeg."° ";
		$_SESSION['ficheParking'] .= $lonSexMin."' ";
		$_SESSION['ficheParking'] .= $lonSexSec."&quot; E , ";
		
		$latSexDeg = (int)($latDec);
		$latSexMinDec = ($latDec-$latSexDeg)*60;
		$latSexMin = (int)($latSexMinDec);
		$latSexSec = round(($latSexMinDec-$latSexMin)*60);

		$_SESSION['ficheParking'] .= $latSexDeg."° ";
		$_SESSION['ficheParking'] .= $latSexMin."' ";
		$_SESSION['ficheParking'] .= $latSexSec."&quot; N";
	//echo($_SESSION['ficheParking']);die();
		

		
		
		// coord départ et arrivée itinéraire routier
		if (OPENSERVICE_ROUTE) {
			$_SESSION['ficheItiDepartLon'] = LON_RDV;
			$_SESSION['ficheItiDepartLat'] = LAT_RDV;
			$_SESSION['ficheItiArriveeLon'] = $_SESSION['trk'][0]['lon'];
			$_SESSION['ficheItiArriveeLat'] = $_SESSION['trk'][0]['lat'];
		}
		
		
		$iDernier = count($_SESSION['trk'])-1;

		// traitement des points de passage wpt pour un fichier séparé
		if (isset($fichierWpt)) {
			$fp = fopen($fichierWpt, "rb");
			clearstatcache();
			$chaineGpx = fread($fp, filesize($fichierWpt));

			fclose($fp);
			$message = "";
			$gpx = simplexml_load_string($chaineGpx);
			if ($gpx===false) $message = "Le fichier gpx des points de passage n'est pas un fichier gpx valide.";
			if ($message<>"") {
				$message .= " Le tableau de marche ne peut pas être construit.";
				alerterEtRetour($message);
				die();
			}
		}
		
		
		// contrôle de l'existence d'au moins un point de passage
		$nbWpt = 0;
		foreach($gpx->wpt as $wpt) {$nbWpt++;}
		if ($nbWpt<1) {
	//	    $message .= "Le fichier gpx ne contient aucun waypoint valide alors qu'il en faut au moins un.";
		}

		if ($message<>"") {
			$message .= " Le tableau de marche ne peut pas être construit.";
			alerterEtRetour($message);
			die();
		}
	if ($nbWpt>0) {
		// construction de $_GLOBALS['wpt']
		$i = 0;
		foreach($gpx->wpt as $wpt) {
			$_GLOBALS['wpt'][$i]['lat'] = (float) $wpt['lat'];
			$_GLOBALS['wpt'][$i]['lon'] = (float) $wpt['lon'];
			$_GLOBALS['wpt'][$i]['name'] = htmlspecialchars_decode((string) $wpt->name);
			if (isset($wpt->desc)) $_GLOBALS['wpt'][$i]['observ'] = htmlspecialchars_decode((string) $wpt->desc);
			else $_GLOBALS['wpt'][$i]['observ'] = "";
			$i++;
		}
	}
	else { // ajout du point de départ et du point d'arrivée
		$_GLOBALS['wpt'][0]['lat'] = $_SESSION['trk'][0]['lat'];
		$_GLOBALS['wpt'][0]['lon'] = $_SESSION['trk'][0]['lon'];
		$_GLOBALS['wpt'][0]['name'] = "Départ";
		$_GLOBALS['wpt'][1]['lat'] = $_SESSION['trk'][$iDernier]['lat'];
		$_GLOBALS['wpt'][1]['lon'] = $_SESSION['trk'][$iDernier]['lon'];
		$_GLOBALS['wpt'][1]['name'] = "Arrivée";

	}
	
	// calcul de ficheTajetKm et temps
	$coordDepart = $_SESSION['trk'][0]['lon'].",".$_SESSION['trk'][0]['lat'];

		// calcul de distance entre chaque point de trace et le précédent
		$distanceTotale = 0;
		foreach($_SESSION['trk'] as $i =>$trkpt) {
			if ($i>0) {
				$d = distance($trkpt['lat'], $trkpt['lon'], $_SESSION['trk'][$i-1]['lat'], $_SESSION['trk'][$i-1]['lon']);
				$_SESSION['trk'][$i]['distance'] = $d;
				$distanceTotale += $d;
			}
			else {
				$_SESSION['trk'][$i]['distance'] = 0;
			}
		}

		// calcul de l'azimut entre chaque point de trace (sauf le dernier !) et le point de trace suivant
		$trkCount = count($_SESSION['trk'])-1;
		foreach($_SESSION['trk'] as $i =>$trkpt) {
			if ($i<$trkCount) {
	//			$a = azimut($_SESSION['trk'][$i-1], $trkpt);
				$a = azimut( $trkpt, $_SESSION['trk'][$i+1]);
				$_SESSION['trk'][$i]['azimut'] = $a;
			}
			else {
				$_SESSION['trk'][$i]['azimut'] = "N/A";
			}
		}

		if (isset($_GLOBALS['wpt'])) {
		// on cherche à assimiler les wpt au trkpt le plus proche :
		// ie : le nom du wpt est donné au trkpt
		//    si distance <=50m on assimile
		//       si distance <=250m on assimile et on ajoute une * au nom du point de passage
		//          si distance > 250m on conserve le wpt hors trace dans $_SESSION['wpt']
			foreach ($_GLOBALS['wpt'] as $i => $wpt) {
				
				$dipp = distanceIndexPlusProche($wpt);
				$distance = $dipp['distance'];
				$j = $dipp['index'];
				if ($distance>250) {
					// on mémorise le wpt externe à la trace
					$_SESSION['w'][] = $wpt; 
				}
				else {
					$_SESSION['trk'][$j]['name'] = $_GLOBALS['wpt'][$i]['name'];
					if ($distance>50) $_SESSION['trk'][$j]['name'] .= "*";
					if (isset($_SESSION['trk'][$j]['observ']))
						$_SESSION['trk'][$j]['observ'] = $_GLOBALS['wpt'][$i]['observ'];
				}
				}
		}

		// si souhaité
		if (isset($_POST['ajouterDA'])) {
			// ajout éventuel des points de départ et d'arrivée
			if (!isset($_SESSION['trk'][0]['name'])){
				$_SESSION['trk'][0]['name'] = "Départ";
				$_SESSION['trk'][0]['observ'] = "";
			}
			$nTrkpt = count($_SESSION['trk'])-1;
			if (!isset($_SESSION['trk'][$nTrkpt]['name'])){
				$_SESSION['trk'][$nTrkpt]['name'] = "Arrivée";
				$_SESSION['trk'][$nTrkpt]['observ'] = "";
			}
		}
		
		//initialisation de $_SESSION['pdp']
		// avec code lettre au début du nom du point de passage si le nom n'est pas numérique
		$i = 0;
		foreach($_SESSION['trk'] as $j =>$trkpt) {
			if (isset($_SESSION['trk'][$j]['name'])) {
				$_SESSION['pdp'][$i] = $_SESSION['trk'][$j];
				$_SESSION['pdp'][$i]['iTrkpt'] = $j;
				// ajout du code lettre en tête du nom
				$codeLettre = chr(65+$i%26);
				if ($i>=26) $codeLettre = $codeLettre . floor(($i+1)/26);
				// placer le codeLettre calculé sur le nom sans codeLettre si le nom n'est pas numérique
				if (!is_numeric($_SESSION['pdp'][$i]['name'])) {
					$_SESSION['pdp'][$i]['name'] = $codeLettre.": ".substr($_SESSION['pdp'][$i]['name'],nbCarCodeLettre($_SESSION['pdp'][$i]['name']) );
				}
				$i++;
			}
		}

		// trie le tableau selon la clé
		ksort($_SESSION['pdp']);
		
		
		// calcul IbpIndex et initialisation de $_SESSION['ficheIbpIndex']
		if (AU_MOINS_56) {
			// on appelle IBPIndex avec un gpx où les altitude ont été éventellement recalculées
			// construction du fichier gpx
			$xml = construireTrk();
			// écriture du fichier gpx
			$nomFichierTemp = mt_rand(0,1000000).".gpx";
			$fichierCheminComplet = realpath(".")."/tmp/".$nomFichierTemp;
			$fichierCheminWeb = "tmp/".$nomFichierTemp;
			// création du fichier sur le serveur
			$leFichier = fopen($fichierCheminWeb, "x+b");
			fwrite($leFichier,$xml);
			fclose($leFichier);
			// calcul de l'index
			$ibpIndex = indiceIbpIndex($nomFichierTemp, $fichierCheminWeb);
			$_SESSION['ficheIbpIndex'] = $ibpIndex;
			// suppression du fichier gpx
			unlink($fichierCheminWeb);
		}
			


		// initialisation denivAdd, tempsAdd, pause
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$_SESSION['pdp'][$i]['denivAdd'] = "";
			$_SESSION['pdp'][$i]['dureeAdd'] = "";
			$_SESSION['pdp'][$i]['pause'] = "";
		}
		// initialisation heure de départ
			$_SESSION['pdp'][0]['heure'] = 540; // 9h00 en minutes

		// initialisation réduction, lissage, seuil
		$_SESSION['nbMaxTrkpt'] = 1000000;
		$_SESSION['distanceLissage'] = DISTANCELISSAGE_DEF;
		$_SESSION['seuil'] = SEUIL_DEF;
		
		
	}
	// fin initialiserTdm()
	////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////
	// charger un fichier tdm
	function chargerTdm($mode) {

		$_SESSION['idAffiche'] = "tdm";
		$_SESSION['profilVitesse'] = "off";

		if ($mode=="bdTdm") { 
			$nomFichierTdm = $_POST['nomFichierTdm'];
			$nomRando = substr($nomFichierTdm,0,-4);
			$fichier = $_POST['webPathFichierTdm'];
			if ($fp = fopen($fichier, "rb")) {
				clearstatcache();
				$chaineTdm = fread($fp, filesize($fichier));
				fclose($fp);
			}
      }
		else {

			// mode test uniquement à partir du site gpx2tdm
			if ($mode=="test") {
				$fichier = "../CeCILL/".$_POST['fichierTest'];
			}
			else { // standard
				$fichier = $_FILES['fichierTdm']['tmp_name'];
				$nomFichierTdm = $_FILES['fichierTdm']['name'];
				$nomRando = substr($nomFichierTdm,0,-4);
			}


			if ($fp = fopen($fichier, "rb")) {
				clearstatcache();
//				$chaineTdm = fread($fp, filesize($fichier));
				$chaineTdm = '';
				while (!feof($fp)) {
					$ligne = fgets($fp);
					// nettoyage de la chaineTdm pour filtrer les erreurs anciennes
					if (strpos($ligne,'<br />')===FALSE && strpos($ligne,'<b>Warning</b>')===FALSE && strpos($ligne,'<connexion')===FALSE && strpos($ligne,'</connexion')===FALSE) {
						$chaineTdm .= $ligne;
					}
				}
				fclose($fp);
			}
		}
		

		$tdm = simplexml_load_string($chaineTdm);
	//die("tdm : "+$tdm);
		$message = "";
		if ($tdm==false) $message = "Le fichier n'est pas un fichier tdm valide.";
		if ($message<>"") {
			$message .= " Le tableau de marche ne peut pas être construit.";
			alerterEtRetour($message);
			die();
		}

	//	$tdm = new SimpleXMLElement($chaineGpx);
//		$_SESSION['nomRando'] = (string) $tdm->nomRando;
		$_SESSION['nomRando'] = $nomRando;
		$_SESSION['nomGpx'] = (string) $tdm->nomGpx;
		$_SESSION['date'] = (string) $tdm->date;
		$_SESSION['animateur'] = htmlspecialchars_decode((string)$tdm->animateur);
		$_SESSION['vitesse'] = (float) $tdm->vitesse;
		$_SESSION['coefPos'] = (float) $tdm->coefPos;
		$_SESSION['coefNeg'] = (float) $tdm->coefNeg;
		// force la méthode pour IRLPT et CPC
		if (isset($tdm->methode)&&((METRE_EFFORT_AFFICHEE==TRUE)||(PROFIL_VITESSE_AFFICHEE==TRUE)) ) $_SESSION['methode'] = (string) $tdm->methode;
		else $_SESSION['methode'] = METHODE_DEF;
		if (isset($tdm->kPos)) $_SESSION['kPos'] = (float) $tdm->kPos;
		else $_SESSION['kPos'] = K_POS;
		if (isset($tdm->kNeg)) $_SESSION['kNeg'] = (float) $tdm->kNeg;
		else $_SESSION['kNeg'] = K_NEG;
		// force le calcul déniv pour IRLPT et CPC
		if (isset($tdm->calcDeniv)&&((METRE_EFFORT_AFFICHEE==TRUE)||(PROFIL_VITESSE_AFFICHEE==TRUE)) ) $_SESSION['calcDeniv'] = (string) $tdm->calcDeniv;
		else $_SESSION['calcDeniv'] = CALC_DENIV_DEF;
/*		
 * sera transformé en distanceLissage voir plus loin
		if (isset($tdm->lissage)) $_SESSION['lissage'] = (integer) $tdm->lissage;
		else $_SESSION['lissage'] = LISSAGE_DEF;
*/
		if (isset($tdm->distanceLissage)) $_SESSION['distanceLissage'] = (integer) $tdm->distanceLissage;
		else $_SESSION['distanceLissage'] = DISTANCELISSAGE_DEF;



		if (isset($tdm->seuil)) $_SESSION['seuil'] = (integer) $tdm->seuil;
		else $_SESSION['seuil'] = SEUIL_DEF;
		// compatibilité v1.0 & 1.1

		if (isset($tdm->ficheLogoAucun)) $_SESSION['ficheLogoAucun'] = (string) $tdm->ficheLogoAucun;
		else $_SESSION['ficheLogoAucun'] = FALSE;
		// encodage de l'image par défaut
		if (isset($tdm->ficheLogoImage)) $_SESSION['ficheLogoImage'] =(string) $tdm->ficheLogoImage;
		else {
			$file = fopen(LOGO_URL,'rb');
			$data = fread($file,filesize(LOGO_URL));
			fclose($file);
			$_SESSION['ficheLogoImage'] = chunk_split(base64_encode($data));
		}
			// type de l'image
		if (isset($tdm->ficheLogoImageType)) $_SESSION['ficheLogoImageType'] =(string) $tdm->ficheLogoImageType;
		else $_SESSION['ficheLogoImageType'] = substr(LOGO_URL,strrpos(LOGO_URL,".")+1);


		if (isset($tdm->ficheTitreFiche)) $_SESSION['ficheTitreFiche'] = htmlspecialchars_decode((string) $tdm->ficheTitreFiche);
		else $_SESSION['ficheTitreFiche'] = $_SESSION['nomRando'];
		if (isset($tdm->ficheSousTitre)) $_SESSION['ficheSousTitre'] = htmlspecialchars_decode((string) $tdm->ficheSousTitre);
		else $_SESSION['ficheSousTitre'] = "";


		if (isset($tdm->ficheDate)) $_SESSION['ficheDate'] = (string) $tdm->ficheDate;
		else {
			$date = $_SESSION['date'];
			$tab_date = explode("/",$date);
			$jour = (int) $tab_date[0];
			$mois = (int) $tab_date[1];
			$an = (int) $tab_date[2];
			setlocale(LC_TIME, 'fr_FR.UTF8');
			$_SESSION['ficheDate'] = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
		}
		if (isset($tdm->fichePresentation)) $_SESSION['fichePresentation'] = htmlspecialchars_decode((string) $tdm->fichePresentation);
		else $_SESSION['fichePresentation'] = "";
		if (isset($tdm->ficheRemarques)) $_SESSION['ficheRemarques'] = htmlspecialchars_decode((string) $tdm->ficheRemarques);
		else $_SESSION['ficheRemarques'] = "";
		
		if (THEME) {
			if (isset($tdm->ficheThemeCode)) $_SESSION['ficheThemeCode'] = htmlspecialchars_decode((string) $tdm->ficheThemeCode);
			else $_SESSION['ficheThemeCode'] = '';
			if (isset($tdm->ficheThemeDescription)) $_SESSION['ficheThemeDescription'] = htmlspecialchars_decode((string) $tdm->ficheThemeDescription);
			else $_SESSION['ficheThemeDescription'] = '';
		}
		
		if (isset($tdm->ficheNiveau)) $_SESSION['ficheNiveau'] = htmlspecialchars_decode((string) $tdm->ficheNiveau);
		else {
			if (isset($tdm->niveau)) $_SESSION['ficheNiveau'] = htmlspecialchars_decode((string) $tdm->niveau);
			else $_SESSION['ficheNiveau'] = "";
		}
		
		if (isset($tdm->ficheIbpIndex) && IBP) $_SESSION['ficheIbpIndex'] = (string) $tdm->ficheIbpIndex;
		// s'il ny a pas d'IbpIndex, il sera calculé après la construction de la trace : voir plus loin
		
		
		if (isset($tdm->ficheDenivPosFiche)) $_SESSION['ficheDenivPosFiche'] = (string) $tdm->ficheDenivPosFiche;
		else $_SESSION['ficheDenivPosFiche'] = "";
		if (isset($tdm->ficheDenivNegFiche)) $_SESSION['ficheDenivNegFiche'] = (string) $tdm->ficheDenivNegFiche;
		else $_SESSION['ficheDenivNegFiche'] = "";
		if (isset($tdm->ficheLongueurFiche)) $_SESSION['ficheLongueurFiche'] = (string) $tdm->ficheLongueurFiche;
		else $_SESSION['ficheLongueurFiche'] = "";
		if (isset($tdm->ficheDureeFiche)) $_SESSION['ficheDureeFiche'] = (string) $tdm->ficheDureeFiche;
		else $_SESSION['ficheDureeFiche'] = "";
		if (isset($tdm->ficheDifficultes)) $_SESSION['ficheDifficultes'] = htmlspecialchars_decode((string) $tdm->ficheDifficultes);
		else {
			if (isset($tdm->difficultes)) $_SESSION['ficheDifficultes'] = htmlspecialchars_decode((string) $tdm->difficultes);
			else $_SESSION['ficheDifficultes'] = DIFFICULTES;
		}
		if (isset($tdm->ficheCarte)) $_SESSION['ficheCarte'] = htmlspecialchars_decode((string) $tdm->ficheCarte);
		else $_SESSION['ficheCarte'] = CARTE;
		if (isset($tdm->ficheRDV)) $_SESSION['ficheRDV'] = htmlspecialchars_decode((string) $tdm->ficheRDV);
		else $_SESSION['ficheRDV'] = RDV;
		if (isset($tdm->ficheDepart)) $_SESSION['ficheDepart'] = htmlspecialchars_decode((string) $tdm->ficheDepart);
		else $_SESSION['ficheDepart'] = DEPART;
		if (isset($tdm->ficheTrajet)) $_SESSION['ficheTrajet'] = htmlspecialchars_decode((string) $tdm->ficheTrajet);
		else $_SESSION['ficheTrajet'] = TRAJET;

		if (isset($tdm->ficheItiXmlSans)) {
			if ($tdm->ficheItiXmlSans!="") {			
				// construction de la chaîne $chaineTrkSans
				$chaineTrkSans = "<trk><trkseg>";
				foreach ($tdm->ficheItiXmlSans->trk->trkseg->trkpt AS $unTrkpt) {
					$chaineTrkSans .= "<trkpt lat='".(string) $unTrkpt['lat']."' lon='".(string)$unTrkpt['lon']."'></trkpt>";
				}
				$chaineTrkSans .= "</trkseg></trk>";
				$_SESSION['ficheItiXmlSans'] = "<?xml version='1.0' encoding='UTF-8' ?>
<gpx xmlns='http://www.topografix.com/GPX/1/1' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd' version='1.1' creator='gpxRando'>". $chaineTrkSans ."</gpx>" ;
			}		
			else $_SESSION['ficheItiXmlSans'] = "";
		}
		else $_SESSION['ficheItiXmlSans'] = "";



		if (isset($tdm->ficheItiXmlAvec)) {
			if ($tdm->ficheItiXmlAvec!="") {			
				// construction de la chaîne $chaineTrkAvec
				$chaineTrkAvec = "<trk><trkseg>";
				foreach ($tdm->ficheItiXmlAvec->trk->trkseg->trkpt AS $unTrkpt) {
					$chaineTrkAvec .= "<trkpt lat='".(string) $unTrkpt['lat']."' lon='".(string)$unTrkpt['lon']."'></trkpt>";
				}
				$chaineTrkAvec .= "</trkseg></trk>";
				$_SESSION['ficheItiXmlAvec'] = "<?xml version='1.0' encoding='UTF-8' ?>
<gpx xmlns='http://www.topografix.com/GPX/1/1' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd' version='1.1' creator='gpxRando'>". $chaineTrkAvec ."</gpx>" ;
			}		
			else $_SESSION['ficheItiXmlAvec'] = "";
		}
		else $_SESSION['ficheItiXmlAvec'] = "";
		
		if (isset($tdm->ficheItiModeVisualiserSans)) $_SESSION['ficheItiModeVisualiserSans'] = (string) $tdm->ficheItiModeVisualiserSans;
		else $_SESSION['ficheItiModeVisualiserSans'] = "";
		
		if (isset($tdm->ficheItiModeVisualiserAvec)) $_SESSION['ficheItiModeVisualiserAvec'] = (string) $tdm->ficheItiModeVisualiserAvec;
		else $_SESSION['ficheItiModeVisualiserAvec'] = "";
		
		if (isset($tdm->ficheItiTitrePageSans)) $_SESSION['ficheItiTitrePageSans'] = (string) $tdm->ficheItiTitrePageSans;
		else $_SESSION['ficheItiTitrePageSans'] = "";
		
		if (isset($tdm->ficheItiTitrePageAvec)) $_SESSION['ficheItiTitrePageAvec'] = (string) $tdm->ficheItiTitrePageAvec;
		else $_SESSION['ficheItiTitrePageAvec'] = "";

		if (isset($tdm->ficheParking)) $_SESSION['ficheParking'] = htmlspecialchars_decode((string) $tdm->ficheParking);
		else $_SESSION['ficheParking'] = "";
		if (isset($tdm->ficheTrajetKm)) $_SESSION['ficheTrajetKm'] = htmlspecialchars_decode((string) $tdm->ficheTrajetKm);
		else $_SESSION['ficheTrajetKm'] = "";
		if (isset($tdm->ficheCovoiturage)) $_SESSION['ficheCovoiturage'] = htmlspecialchars_decode((string) $tdm->ficheCovoiturage);
		else $_SESSION['ficheCovoiturage'] = "";
		if (isset($tdm->ficheLaRandonnee)) $_SESSION['ficheLaRandonnee'] = htmlspecialchars_decode((string) $tdm->ficheLaRandonnee);
		else $_SESSION['ficheLaRandonnee'] = "";
		if (isset($tdm->ficheEquipement)) $_SESSION['ficheEquipement'] = htmlspecialchars_decode((string) $tdm->ficheEquipement);
		else $_SESSION['ficheEquipement'] = EQUIPEMENT;
		if (isset($tdm->ficheAnimateur)) $_SESSION['ficheAnimateur'] = htmlspecialchars_decode((string) $tdm->ficheAnimateur);
		else $_SESSION['ficheAnimateur'] = htmlspecialchars_decode($_SESSION['animateur']);
		if (isset($tdm->ficheComplementsTitre)) $_SESSION['ficheComplementsTitre'] = htmlspecialchars_decode((string) $tdm->ficheComplementsTitre);
		else $_SESSION['ficheComplementsTitre'] = "";
		if (isset($tdm->ficheComplements)) $_SESSION['ficheComplements'] = htmlspecialchars_decode((string) $tdm->ficheComplements);
		else {
			if (isset($tdm->observations)) $_SESSION['ficheComplements'] = htmlspecialchars_decode((string) $tdm->observations);
			else $_SESSION['ficheComplements'] ="";
		}



		// construction de $_SESSION['trk']
		$i = 0;
		$distanceCumulee = 0;
		foreach($tdm->trk->trkpt as $trkpt) {
			$_SESSION['trk'][$i]['lat'] = (float) $trkpt->lat;
			$_SESSION['trk'][$i]['lon'] = (float) $trkpt->lon;

			if (isset($trkpt->ele)) {
				if ($trkpt->ele!="") $_SESSION['trk'][$i]['ele'] = (integer) $trkpt->ele;
				else $_SESSION['trk'][$i]['ele'] = "";
			}
			else $_SESSION['trk'][$i]['ele'] = ""; // donnée manquante
	//		$_SESSION['trk'][$i]['ele'] = (integer) $trkpt->ele;
			$_SESSION['trk'][$i]['distance'] = (float) $trkpt->distance;
			$distanceCumulee += $_SESSION['trk'][$i]['distance'];
			// on ne prend pas les azimuts du tdm car ils sont peut-^etre faux
			//$_SESSION['trk'][$i]['azimut'] = (float) $trkpt->azimut;
			// si c'est un point de passage, il a un name
			if ($trkpt->name) $_SESSION['trk'][$i]['name'] = htmlspecialchars_decode((string) $trkpt->name);
			$i++;
		}
		// nb de points de trace
		$nbTrkpt = $i;
		$distanceMoyenne = $distanceCumulee/$nbTrkpt;
		
		// transformation de lissage en distanceLissage
		if (isset($tdm->lissage)) { 
			$l = (integer) $tdm->lissage;
			$dl = $distanceMoyenne * $l;
			// arrondi aux dizaines
			$dl = round($dl/10)*10;
			$_SESSION['distanceLissage'] = $dl;
		}
		// remplacer chaque ele=="" par ele la  + proche !=""
		$elePrec = "";
		foreach ($_SESSION['trk'] as $i => $trkpt) {
			if ($trkpt['ele']!="") $elePrec = $trkpt['ele'];
			else {
				if ($elePrec!="") $_SESSION['trk'][$i]['ele'] = $elePrec;
				else {
					$j = $i+1;
					if (isset($_SESSION['trk'][$j])) {
						while ($_SESSION['trk'][$j]=="") {
							$j++;
							if (!isset($_SESSION['trk'][$j])) die("La trace ne contient pas d'attribut ele, elle est donc inutilisable.");
						}
						$_SESSION['trk'][$i]['ele'] = $_SESSION['trk'][$j]['ele'];
					}
					else die("La trace ne contient pas d'attribut ele, elle est donc inutilisable.");
				}
			}
		}


		if (OPENSERVICE_ROUTE) {
			// coord départ et arrivée itinéraire routier
			if (isset($tdm->ficheItiDepartLon)) $_SESSION['ficheItiDepartLon'] = (string) $tdm->ficheItiDepartLon;
			else $_SESSION['ficheItiDepartLon'] = LON_RDV;
			if (isset($tdm->ficheItiDepartLat)) $_SESSION['ficheItiDepartLat'] = (string) $tdm->ficheItiDepartLat;
			else $_SESSION['ficheItiDepartLat'] = LAT_RDV;
			if (isset($tdm->ficheItiArriveeLon)) $_SESSION['ficheItiArriveeLon'] = (string) $tdm->ficheItiArriveeLon;
			else $_SESSION['ficheItiArriveeLon'] = $_SESSION['trk'][0]['lon'];
			if (isset($tdm->ficheItiArriveeLat)) $_SESSION['ficheItiArriveeLat'] = (string) $tdm->ficheItiArriveeLat;
			else $_SESSION['ficheItiArriveeLat'] = $_SESSION['trk'][0]['lat'];
		}
		
		
		// recalcul des azimut pour corriger les anciens tdm avec azimmuts faux
		// calcul de l'azimut entre chaque point de trace (sauf le dernier !) et le point de trace suivant
		$trkCount = count($_SESSION['trk'])-1;
		$j = 0; // indice des pdp
		foreach($_SESSION['trk'] as $i =>$trkpt) {
			if ($i<$trkCount) {
	//			$a = azimut($_SESSION['trk'][$i-1], $trkpt);
				$a = azimut( $trkpt, $_SESSION['trk'][$i+1]);
				$_SESSION['trk'][$i]['azimut'] = $a;
			}
			else {
				$_SESSION['trk'][$i]['azimut'] = "N/A";
			}
			// si le point de trace est un point de passage on affecte l'azimut du point de passage
			if (isset($_SESSION['trk'][$i]['name'])) {
				$_SESSION['pdp'][$j]['azimut'] = $_SESSION['trk'][$i]['azimut'];
				$j++;
			}
		}

		// construction de $_SESSION['pdp']
		$i = 0;
		foreach($tdm->pdp->pdppt as $pdppt) {
			$_SESSION['pdp'][$i]['lat'] = (float) $pdppt->lat;
			$_SESSION['pdp'][$i]['lon'] = (float) $pdppt->lon;
			
			$_SESSION['pdp'][$i]['ele'] = (integer) $pdppt->ele;
			$_SESSION['pdp'][$i]['distance'] = (float) $pdppt->distance;
			// on ne prend pas l'azimut enregistré qui peut ^etre faux
			//$_SESSION['pdp'][$i]['azimut'] = (float) $pdppt->azimut;
			
			$name = (string) $pdppt->name;

			$codeLettre = chr(65+$i%26);
			if ($i>=26) $codeLettre = $codeLettre . floor(($i+1)/26);
			// si pas numérique
			if (!is_numeric($name)) {
				$name = $codeLettre.": ".substr($name,nbCarCodeLettre($name));
			}

			$_SESSION['pdp'][$i]['name'] = htmlspecialchars_decode($name);
			$_SESSION['pdp'][$i]['iTrkpt'] = (integer) $pdppt->iTrkpt;
			if ($pdppt->denivAdd=="") $_SESSION['pdp'][$i]['denivAdd'] = "";
			else $_SESSION['pdp'][$i]['denivAdd'] = (integer) $pdppt->denivAdd;
			if ($pdppt->dureeAdd=="") $_SESSION['pdp'][$i]['dureeAdd'] = "";
			else $_SESSION['pdp'][$i]['dureeAdd'] = (integer) $pdppt->dureeAdd;
			// compatibilité v 1.0
			if (isset($pdppt->pause)) {
				if ($pdppt->pause=="") $_SESSION['pdp'][$i]['pause'] = "";
				else $_SESSION['pdp'][$i]['pause'] = (integer) $pdppt->pause;
			}
			else {
				if ($i!=0) {
					$_SESSION['pdp'][$i-1]['pause'] = $_SESSION['pdp'][$i]['dureeAdd'];
					$_SESSION['pdp'][$i]['dureeAdd'] = "";
					$_SESSION['pdp'][$i]['pause'] = "";
				}
			}
			$_SESSION['pdp'][$i]['observ'] = htmlspecialchars_decode((string) $pdppt->observ);
			$_SESSION['pdp'][$i]['distanceCumul'] = (float) $pdppt->distanceCumul;
			$_SESSION['pdp'][$i]['deniv'] = (integer) $pdppt->deniv;
			$_SESSION['pdp'][$i]['denivCumulPos'] = (integer) $pdppt->denivCumulPos;
			$_SESSION['pdp'][$i]['denivCumulNeg'] = (integer) $pdppt->denivCumulNeg;
			$_SESSION['pdp'][$i]['pente'] = (float) $pdppt->pente;
			$_SESSION['pdp'][$i]['duree'] = (integer) $pdppt->duree;
			$_SESSION['pdp'][$i]['heure'] = (integer) $pdppt->heure;
			$i++;
		}
		
      // construction de S_SESSION['w']
      if (isset($tdm->w)) { 
         $i = 0;
         foreach($tdm->w->wpt as $wpt) {
            $_SESSION['w'][$i]['lat'] = (float) $wpt->lat;
            $_SESSION['w'][$i]['lon'] = (float) $wpt->lon;
            $_SESSION['w'][$i]['name'] = htmlspecialchars_decode((string) $wpt->name);		
            $i++;
         }
      }
	
		// calcul de l'IbpIndex éventuellement manquant
		if (IBP && (!isset($_SESSION['ficheIbpIndex']) || $_SESSION['ficheIbpIndex']=="")) {
			// construction du fichier gpx
			$xml = construireTrk();
			// écriture du fichier gpx
			$nomFichierTemp = mt_rand(0,1000000).".gpx";
			$fichierCheminComplet = realpath(".")."/tmp/".$nomFichierTemp;
			$fichierCheminWeb = "tmp/".$nomFichierTemp;
			// création du fichier sur le serveur
			$leFichier = fopen($fichierCheminWeb, "x+b");
			fwrite($leFichier,$xml);
			fclose($leFichier);
			// calcul de l'index
			$ibpIndex = indiceIbpIndex($nomFichierTemp, $fichierCheminWeb);
			$_SESSION['ficheIbpIndex'] = $ibpIndex;
			// suppression du fichier gpx
			unlink($fichierCheminWeb);
		}	

		// calcul du niveau
		if (NIVEAU_PAR_IBP) {
         if (isset($_SESSION['ficheIbpIndex'])) {
            if ($_SESSION['ficheIbpIndex']<=50) $_SESSION['ficheNiveau'] = "F";
            else
               if ($_SESSION['ficheIbpIndex']<=75) $_SESSION['ficheNiveau'] = "M";
               else 
                  if ($_SESSION['ficheIbpIndex']<=100) $_SESSION['ficheNiveau'] = "M+";
                  else $_SESSION['ficheNiveau'] = "D";
         }
         else {
            $_SESSION['ficheNiveau'] = "";
         }
      }
	}
	// fin charger
	////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////
	// Calcul du tableau de marche
	function calculerTdm() {
	//var_dump($_SESSION['trk']); die();
		
		
		// enregistrer le lastUpdateSession pour pouvoir le passer à javascript
		$GLOBALS['lastUpdateSession'] = time(); //temps en secondes depuis 01/01/1970
		// calcul des coordonnées UTM des pdp
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$_SESSION['pdp'][$i]['utm'] = geo2utm($pdp['lat'],$pdp['lon']);
		}

		// distance entre 2 pdp
		$jPrec = 0;
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$distCumulTrk = 0;
			for($j=$jPrec;$j<=$pdp['iTrkpt'];$j++) {
				$distCumulTrk += $_SESSION['trk'][$j]['distance'];
			}
			$_SESSION['pdp'][$i]['distance'] = $distCumulTrk;
			$jPrec = $pdp['iTrkpt']+1;
			if ($i==0) 	$_SESSION['pdp'][$i]['distanceCumul'] = 0;
			else $_SESSION['pdp'][$i]['distanceCumul'] = $_SESSION['pdp'][$i-1]['distanceCumul']+$_SESSION['pdp'][$i]['distance'];
		}

		$distanceTotaleKm = $_SESSION['pdp'][$i]['distanceCumul']/1000;
//var_dump($_SESSION['pdp']); die();
		
		// dénivelée entre 2 pdp
		$denivCumul = 0;
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			if ($i==0) {
				$_SESSION['pdp'][$i]['deniv'] = 0;
				$_SESSION['pdp'][$i]['denivCumulPos'] = 0;
				$_SESSION['pdp'][$i]['denivCumulNeg'] = 0;
			}
			else {
				$_SESSION['pdp'][$i]['deniv'] = $_SESSION['pdp'][$i]['ele']-$_SESSION['pdp'][$i-1]['ele'];
				if ($_SESSION['pdp'][$i]['deniv']>0) {
					$_SESSION['pdp'][$i]['denivCumulPos'] = $_SESSION['pdp'][$i-1]['denivCumulPos']+$_SESSION['pdp'][$i]['deniv'];
					$_SESSION['pdp'][$i]['denivCumulNeg'] = $_SESSION['pdp'][$i-1]['denivCumulNeg'];
				}
				else {
					$_SESSION['pdp'][$i]['denivCumulNeg'] = $_SESSION['pdp'][$i-1]['denivCumulNeg']+$_SESSION['pdp'][$i]['deniv'];
					$_SESSION['pdp'][$i]['denivCumulPos'] = $_SESSION['pdp'][$i-1]['denivCumulPos'];
				}
			}
				// pour les cumuls on ajoute en pos et en neg la dénivelée additionnelle
				$_SESSION['pdp'][$i]['denivCumulPos'] += $_SESSION['pdp'][$i]['denivAdd'];
				$_SESSION['pdp'][$i]['denivCumulNeg'] -= $_SESSION['pdp'][$i]['denivAdd'];

		}

		// trace réduite : nbMaxTrkpt

		$trkModifiee = $_SESSION['trk'];
	//	$dernier = count($trkModifiee)-2;

		// trace lissée : lissage
		// suppression des points à ele inconnue <-32766
		$j = 0;
		foreach ($trkModifiee as $i => $trkpt) {
			if ($trkpt['ele']>-32767) {
				$trkModifieeEle[$j] = $trkpt;
				$j++;
			}
		}
/*		
		// lissage : moyenne mobile sur $_SESSION['lissage'] points
		if ($_SESSION['lissage']>0) {
			$dec = floor($_SESSION['lissage']/2);
			$n = count($trkModifieeEle)-1;
			foreach ($trkModifieeEle as $i => $trkpt) {
				// sauf le premier et le dernier
				if ($i>0 && $i<$n) {
					$cumul = 0;
					$m =0;
					for ($k=$i-$dec; $k<=$i+$dec; $k++) {
						if ($k>0 && $k<$n) {
							$cumul += $trkModifieeEle[$k]['ele'];
							$m++;
						}
					}
					$trkLisse[$i] = $trkpt;
					$trkLisse[$i]['ele'] = round($cumul/$m);
				}
				else $trkLisse[$i] = $trkpt;
			}
		}
		else $trkLisse = $trkModifieeEle;
*/	
		////////////////////////////////////////////////////////////////////////////
		// lissage par distance de lissage : moyenne mobile sur distance de lissage
		// $_SESSION['distanceLissage'] en mètres
		////////////////////////////////////////////////////////////////////////////
//var_dump($trkModifieeEle); die;
		
		if ($_SESSION['calcDeniv']=="trace") {
			$trkLisse = $trkModifieeEle; // ici : trace à lisser
			if ($_SESSION['distanceLissage']>0) {

				// ajout des distances cumulées à $trkModifieeEle
				$trkModifieeEle[0]['distCumulee'] = 0;
				$n = count($trkModifieeEle)-1;
				foreach ($trkModifieeEle as $i => $trkpt) {
					// sauf le premier
					if ($i>0) {
						$trkModifieeEle[$i]['distCumulee'] = $trkModifieeEle[$i-1]['distCumulee']+distance($trkModifieeEle[$i-1]['lat'],$trkModifieeEle[$i-1]['lon'], $trkModifieeEle[$i]['lat'],$trkModifieeEle[$i]['lon']);
					}
				}
				
				// calcul de $trkLisse
				$n = count($trkModifieeEle)-1;
				$demiDistance = floor($_SESSION['distanceLissage']/2);
//var_dump($trkModifieeEle);die;
				foreach ($trkModifieeEle as $j => $trkpt) {
					$cumulEle = $trkpt['ele'];
					$nbPoints = 1;
					// avant : 
					$k = $j-1;
					while ($k>=0 && ($trkModifieeEle[$j]['distCumulee']-$trkModifieeEle[$k]['distCumulee'])<$demiDistance) {
						$nbPoints++;
						$cumulEle += $trkModifieeEle[$k]['ele'];
						$k--;
					}
					// après :
					$k = $j+1;
					while ($k<$n && ($trkModifieeEle[$k]['distCumulee']-$trkModifieeEle[$j]['distCumulee'])<$demiDistance) {
						$nbPoints++;
						$cumulEle += $trkModifieeEle[$k]['ele'];
						$k++;
					}
//if ($j==332) die($nbPoints.' points ; '.$cumulEle.' cumul ele');					
					// moyenne :
					$trkLisse[$j]['ele'] = round($cumulEle/$nbPoints);
				}
				// passage en session de la trace lissée pour enregistrement éventuel du gpx lissé
				$_SESSION['trkLisse'] = $trkLisse;
			}
			else $trkLisse = $trkModifieeEle;
//var_dump($trkLisse);die();			
			////////////////////////////////////////////////////////////////////////////

			// seuil
			
			// calcul des dénivelées brutes (entre les points de trace)
			$_SESSION['denivBrutePos'] = 0;
			$_SESSION['denivBruteNeg'] = 0;
			$elePrec = -32768;
			$dernier = count($trkLisse)-1;

			foreach ($trkLisse as $i =>$trkpt) {
				if ($i==0) $elePrec = $trkpt['ele'];
				else {
					if ($elePrec>-32766) {
						if ($trkpt['ele']>-32766) {
							$deniv = $trkpt['ele']-$elePrec;
							if (abs($deniv)>=$_SESSION['seuil'] || $i==$dernier) {
								if ($deniv>0) {	$_SESSION['denivBrutePos'] +=  $deniv;}
								else {$_SESSION['denivBruteNeg'] +=  $deniv;}
								$elePrec = $trkpt['ele'];
							}
						}
					}
				}
			}

			// nbre de points de trace INUTILISÉ !!!!
			$_SESSION['nbTrkpt'] = $i+1;
			
		/////////////////////////////////////////////////////////////////////////////////////
		// calcul du temps de marche entre les points de trace
		/////////////////////////////////////////////////////////////////////////////////////
		if ($_SESSION['calcDeniv']=="trace") {
			$distancePos = 0;
			$distanceNeg = 0;
			$denivPos = 0;
			$denivNeg = 0;
			$duree = 0;
			$j = 0;
			$iPrec = 0;
			$dist = 0;
//var_dump($trkLisse);die;
			foreach ($trkLisse as $i => $unPointTrace) {
				if ($i>0) {
					$deniv = $trkLisse[$i]['ele']-$trkLisse[$iPrec]['ele'];
					$dist += distance($trkLisse[$i-1]['lat'],$trkLisse[$i-1]['lon'],$trkLisse[$i]['lat'],$trkLisse[$i]['lon']);
					// prise en compte du seuil sauf si point de passage
					if (abs($deniv)>$_SESSION['seuil'] || isset($unPointTrace['name'])) {
						if ($deniv>=0) {
							$distancePos += $dist;
							$denivPos += $deniv;
							if ($_SESSION['methode']=="effort") {
								// distance
								$duree += $dist /1000 / $_SESSION['vitesse'] * 60;
								// déniv
								$duree += $deniv /1000 * $_SESSION['coefPos'] / $_SESSION['vitesse'] *60;
							}
							else { // vitesse selon pente
								if ($dist>0){
									$pente = round($deniv / $dist *100);
									if ($pente>100) $pente = 100; if ($pente<-100) $pente = -100;
									// durée pour pente
									$k = $_SESSION['kPos'];
									if (($pente % 2) == 0) {
										$duree += $dist/1000/(pow($GLOBALS['profilVitesse'][$pente],$k)*$_SESSION['vitesse'])*60;
									}
									else {
										$duree += $dist/1000/(pow(($GLOBALS['profilVitesse'][$pente-1]+$GLOBALS['profilVitesse'][$pente+1])/2,$k)*$_SESSION['vitesse'])*60;
									}
								}
							}
						}
						else {  // deniv neg
							$distanceNeg += $dist;
							$denivNeg += $deniv;
							if ($_SESSION['methode']=="effort") {
								// distance
								$duree += $dist /1000 / $_SESSION['vitesse'] * 60;
								// déniv
								$duree += -$deniv /1000 * $_SESSION['coefNeg'] / $_SESSION['vitesse'] *60;
							}
							else { // vitesse selon pente
								if ($dist>0){
									$pente = round($deniv / $dist *100);
									if ($pente>100) $pente = 100; if ($pente<-100) $pente = -100;
									// durée pour pente
									$k = $_SESSION['kNeg'];
									if (($pente % 2) == 0) {
										$duree += $dist/1000/(pow($GLOBALS['profilVitesse'][$pente],$k)*$_SESSION['vitesse'])*60;
									}
									else {
										$duree += $dist/1000/(pow(($GLOBALS['profilVitesse'][$pente-1]+$GLOBALS['profilVitesse'][$pente+1])/2,$k)*$_SESSION['vitesse'])*60;
									}
								}
							}
						}
						$dist = 0;
						$iPrec = $i;
					}
				}
				if (isset($unPointTrace['name'])) {
					$_SESSION['pdp'][$j]['distancePos'] = $distancePos;
					$_SESSION['pdp'][$j]['distanceNeg'] = $distanceNeg;
					$_SESSION['pdp'][$j]['denivPos'] = $denivPos;
					$_SESSION['pdp'][$j]['denivNeg'] = $denivNeg;
					if ($distancePos>0) $_SESSION['pdp'][$j]['pentePos'] = round($denivPos / $distancePos *100)/100;
					else $_SESSION['pdp'][$j]['pentePos'] = 0;
					if ($distanceNeg>0) $_SESSION['pdp'][$j]['penteNeg'] = round($denivNeg / $distanceNeg *100)/100;
					else $_SESSION['pdp'][$j]['penteNeg'] = 0;
					$_SESSION['pdp'][$j]['duree'] = $duree + $_SESSION['pdp'][$j]['dureeAdd'];
					$distancePos = 0;
					$distanceNeg = 0;
					$denivPos = 0;
					$denivNeg = 0;
					$duree = 0;
					$j++;
				}
			}
			
		}
		/////////////////////////////////////////////////////////////////////////////////////
		// fin du calcul du temps de marche entre les points de trace
		/////////////////////////////////////////////////////////////////////////////////////
			
		} // fin si calcDeniv == 'trace'
		

		/////////////////////////////////////////////////////////////////////////////////////
		// calcul du temps de marche entre les points de passage
      /////////////////////////////////////////////////////////////////////////////////////
		if ($_SESSION['calcDeniv']=="passage") {
			// pente entre 2 pdp
			foreach($_SESSION['pdp'] as $i =>$pdp) {
				if ($i==0) $_SESSION['pdp'][$i]['pente'] = 0;
				else
					if ($_SESSION['pdp'][$i]['distance']==0) $_SESSION['pdp'][$i]['pente'] = 0;
					else $_SESSION['pdp'][$i]['pente'] = $_SESSION['pdp'][$i]['deniv'] / $_SESSION['pdp'][$i]['distance'];
			}
			// durée de marche entre 2 pdp méthode km-effort
			if ($_SESSION['methode']=="effort") {
				foreach($_SESSION['pdp'] as $i =>$pdp) {
					// distance
					$duree = $pdp['distance'] /1000 / $_SESSION['vitesse'] * 60;
					// deniv
					if ($pdp['deniv']>0) $duree += $pdp['deniv'] /1000 * $_SESSION['coefPos'] / $_SESSION['vitesse'] *60;
					else $duree += -$pdp['deniv'] /1000 * $_SESSION['coefNeg'] / $_SESSION['vitesse'] * 60;
					// denivAdd
					if ($pdp['denivAdd']<>"") $duree += $pdp['denivAdd'] /1000 * $_SESSION['coefPos'] / $_SESSION['vitesse'] *60 + $pdp['deniv'] /1000 * $_SESSION['coefNeg'] / $_SESSION['vitesse'] * 60;
					$_SESSION['pdp'][$i]['duree'] = round($duree);
					// temps de marche additionnel
					$_SESSION['pdp'][$i]['duree'] += $pdp['dureeAdd'];

			//		$_SESSION['pdp'][$i]['duree'] = tdm($pdp['distance'],$pdp['deniv'], $pdp['denivAdd']);
				}
			}
			// durée de marche entre 2 pdp méthode profil de vitesse
			else {
				foreach($_SESSION['pdp'] as $i =>$pdp) {
					$duree = 0;
					// signe de la deniv add
					if ($pdp['deniv']>0) $denivAdd = -1*abs($pdp['denivAdd']);
					else $denivAdd = abs($pdp['denivAdd']);
					//  test de division par 0
					if ((abs($pdp['deniv'])+2*abs($denivAdd))!=0) {
						// distance en m pour deniv additionnel
						$D1 = abs($denivAdd)/(abs($pdp['deniv'])+2*abs($denivAdd))*$pdp['distance'] ;
						// distance en m pour deniv + deniv add
						$D2 = ($pdp['distance']-$D1);
						// durée pour pente1
						if ($D1!=0) {
							$pente1 = round($denivAdd / $D1 *100);
							if ($pente1>100) $pente1 = 100; if ($pente1<-100) $pente1 = -100;
							// durée pour pente1
							if ($pente1>0) $k = $_SESSION['kPos']; else $k = $_SESSION['kNeg'];
							if (($pente1 % 2) == 0) {
								$duree += $D1/1000/(pow($GLOBALS['profilVitesse'][$pente1],$k)*$_SESSION['vitesse'])*60;
							}
							else {
								$duree += $D1/1000/(pow(($GLOBALS['profilVitesse'][$pente1-1]+$GLOBALS['profilVitesse'][$pente1+1])/2,$k)*$_SESSION['vitesse'])*60;
							}
						}
					}
					else $D2 = $pdp['distance'];
					if ($D2!=0) {
						// durée pour pente2
						$pente2 = round(($pdp['deniv']-$denivAdd )/ $D2 *100);
						if ($pente2>100) $pente2 = 100; if ($pente2<-100) $pente2 = -100;
						if ($pente2>0) $k = $_SESSION['kPos']; else $k = $_SESSION['kNeg'];
						if (($pente2 % 2) == 0) {
							$duree += $D2/1000/(pow($GLOBALS['profilVitesse'][$pente2],$k)*$_SESSION['vitesse'])*60;
						}
						else {
							$duree += $D2/1000/(pow(($GLOBALS['profilVitesse'][$pente2-1]+$GLOBALS['profilVitesse'][$pente2+1])/2,$k)*$_SESSION['vitesse'])*60;
						}
					}
					else $duree = 0;
					// temps de marche additionnel
					$_SESSION['pdp'][$i]['duree'] = $duree+$pdp['dureeAdd'];
				}
			}
		}
		/////////////////////////////////////////////////////////////////////////////////////
		// fin du calcul du temps de marche entre les points de passage
		/////////////////////////////////////////////////////////////////////////////////////
		// heure d'arrivée : on ajoute le temps de pause de la ligne précédente
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			if ($i!=0) $_SESSION['pdp'][$i]['heure'] = $_SESSION['pdp'][$i-1]['heure'] + $_SESSION['pdp'][$i]['duree'] +$_SESSION['pdp'][$i-1]['pause'];

		}
		
		/////////////////////////////////////////////////////////////////////////////////////
		// calcul des altitudes min et max
      /////////////////////////////////////////////////////////////////////////////////////
      $altMin = 10000;
      $altMax = -1000;
		// selon trace lissée ou non
		if (isset($_SESSION['trkLisse'])) {
         foreach ($_SESSION['trkLisse'] AS $unTrk) {
            if ($unTrk['ele']>$altMax) $altMax = $unTrk['ele']; 
            if ($unTrk['ele']<$altMin) $altMin = $unTrk['ele']; 
         }
		}
		else {
         foreach ($_SESSION['trk'] AS $unTrk) {
            if ($unTrk['ele']>$altMax) $altMax = $unTrk['ele']; 
            if ($unTrk['ele']<$altMin) $altMin = $unTrk['ele']; 
         }
		}
		$_SESSION['altitudeMax'] = $altMax;
		$_SESSION['altitudeMin'] = $altMin;

	}
	// fin calculerTdm
	////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////
	// affichage
	function afficherTdm($action){
/*
 Menus déroulants :
 https://www.creativejuiz.fr/blog/tutoriels/menu-deroulant-css3-transition-max-height
 https://www.hostinger.fr/tutoriels/menu-deroulant-css/
 https://www.pierre-giraud.com/html-css-apprendre-coder-cours/creation-menu-deroulant/
 https://red-team-design.developpez.com/tutoriels/css/menu-deroulant-css3/
 http://www.ellm.net/labo/labo_menu.html
*/
		
	// calcul des cumuls pour la synthèse
		$n = count($_SESSION['pdp'])-1;
		$distanceCumul = round($_SESSION['pdp'][$n]['distanceCumul']/1000,1);
		if ($_SESSION['calcDeniv']=="passage") {
			$denivCumulPos = $_SESSION['pdp'][$n]['denivCumulPos'];
			$denivCumulNeg = $_SESSION['pdp'][$n]['denivCumulNeg'];
		}
		else {
			$denivCumulPos = 0;
			$denivCumulNeg = 0;
			foreach ($_SESSION['pdp'] as $i => $unPdp) {
				$denivCumulPos += $unPdp['denivPos'];
				$denivCumulNeg += $unPdp['denivNeg'];
			}
		}
		$dureeCumul = 0;
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$dureeCumul += $_SESSION['pdp'][$i]['duree'];
		}

		// initialisation des valeurs-cumuls de la fiche rando
		if ($_SESSION['ficheDenivPosFiche']=="") $_SESSION['ficheDenivPosFiche'] = $denivCumulPos;
		if ($_SESSION['ficheDenivNegFiche']=="") $_SESSION['ficheDenivNegFiche'] = abs($denivCumulNeg);
		if ($_SESSION['ficheLongueurFiche']=="") $_SESSION['ficheLongueurFiche'] = $distanceCumul;
		if ($_SESSION['ficheDureeFiche']=="") $_SESSION['ficheDureeFiche'] = $dureeCumul;

		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
		<head>
			<meta content="text/html; charset=UTF-8" http-equiv="content-type">
			<title><?php if ($action!="imprimerTdm") echo "gpx2tdm Tableau de marche"; else echo "gpx2tdm à imprimer";?></title>
			<link rel="shortcut icon" type="image/png" href="images/gpx2tdm_ico.png" />
			
			<script type="text/javascript" src="js/jquery.js"></script>

			<link rel="stylesheet" href="css/gpx2tdmScreen.css" type="text/css" media="screen" />
			<link rel="stylesheet" href="css/gpx2tdmPrint.css" type="text/css" media="print" />

			
			<script type="text/javascript">
<?php
	// on ne peut pas le charger par script url= car il contient du php qui doit être interprété
	include("js/gpx2tdm.js");
?>
			</script>

	<?php
		if ((NIVEAU_LISTE || !UN_ANIMATEUR) && $action!="imprimerTdm") {
	?>
			<script type="text/javascript">
				if(window.addEventListener){
					window.addEventListener('load', actualiser, false);
				}else{
					window.attachEvent('onload', actualiser);
				}	
				
				function actualiser() {
<?php 
	if (!UN_ANIMATEUR) {
?>
					actualiserAnimateur();
<?php
	}
?>
<?php 
	if (NIVEAU_LISTE) {
?>
					actualiserNiveau();
<?php
	}
?>
				} // fin actualiser()
			
				function actualiserAnimateur() {
					var animateur=document.getElementById('animateur');
					var anim1 = document.getElementById('selAnimateur1').value;
					var anim2 = document.getElementById('selAnimateur2').value;
					animateur.value = anim1;
					
					if (anim2!="") 
						if (anim1!="") animateur.value += ", "+anim2;
						else  animateur.value = anim2;
				}
				
				function actualiserNiveau() {
	//alert("selNiveau = "+document.getElementById('selNiveau'));
					document.getElementById('ficheNiveau').value = document.getElementById('selNiveau').value;
				}
			</script>
<?php
			}
?>


		</head>
		<body
		<?php if ($action!="imprimerTdm") {
		?>
			onLoad = " <?php if ($_POST['newAction'] == "creer" || $_POST['newAction'] == "charger") echo("$('#inputDate').trigger( 'click' );"); //click() ?>  initialiserGraphiqueOngletEtReactiverSession('<?php echo($_SESSION['idAffiche']); ?>', '<?php echo $GLOBALS['lastUpdateSession']*1000;?>');

			<?php
				// initialiser $xml
				$xml = "";

				// envoi du formulaire selon action
				if ($action=="envoyerTdm") {
					echo (" document.getElementById('newAction').value = 'envoyerTdm'; document.getElementsByName('agir')[0].submit();");
				}
				if ($action=="envoyerTrk") {
					echo (" document.getElementById('newAction').value = 'envoyerTrk'; document.getElementsByName('agir')[0].submit();");
				}
				if ($action=="envoyerTrkLisse") {
					echo (" document.getElementById('newAction').value = 'envoyerTrkLisse'; document.getElementsByName('agir')[0].submit();");
				}
				if ($action=="envoyerWpt") {
					echo (" document.getElementById('newAction').value = 'envoyerWpt'; document.getElementsByName('agir')[0].submit();");
				}
				if ($action=="afficherCarteAuto") {
					$xml = construireTrk();
					echo ("document.getElementById('titrePage').value = document.getElementById('nomRando').value; document.getElementById('modeVisualiser').value = 'auto'; document.getElementById('formVisualiser').submit();");
				}
				if ($action=="afficherCartePortrait") {
					$xml = construireTrk();
					echo ("document.getElementById('titrePage').value = document.getElementById('nomRando').value;
					document.getElementById('modeVisualiser').value = 'portrait';
					document.getElementById('formVisualiser').submit();");
				}
				if ($action=="afficherCartePaysage") {
					$xml = construireTrk();
					echo ("document.getElementById('titrePage').value = document.getElementById('nomRando').value; document.getElementById('modeVisualiser').value = 'paysage'; document.getElementById('formVisualiser').submit();");
				}
				
 				if ($action=="imprimeFicheHtml") {
					echo (" document.getElementsByName('imprimeFicheHtml')[0].submit();");
				}
				if ($action=="imprimeFichePdf") {
					echo (" document.getElementsByName('imprimeFichePdf')[0].submit();");
				}
				if ($action=="analyser") {
					echo (" document.getElementById('newAction').value = 'analyser'; document.getElementsByName('agir')[0].submit();");
				}
				
			?>

			">
				<form  action="index.php" name="formRetour" id="formRetour" target="_self">
				</form>

  				<form enctype="multipart/form-data" method="post" action="<?php echo $GLOBALS['urlCarte'];?>" name="formVisualiserIti" id="formVisualiserIti"  target="_blank">
					<input type="hidden" name="origine" value="gpx2tdm" >

					<input type="hidden" name="zoom" value="11" >
					
					<input type="hidden" name="modeVisualiser" id="modeVisualiserIti" value="" >
					<input type="hidden" name="etiquette" id="etiquetteIti" value="">
					<input type="hidden" name="titrePage" id="titrePageIti" value="">
					<input type="hidden" name="xml" id="xmlIti" value='' >
					
					<input type="hidden" name="xmlAvec" id="xmlAvec" value='' >
					<input type="hidden" name="xmlSans" id="xmlSans" value='' >
					<input type="hidden" name="modeVisualiserAvec" id="modeVisualiserAvec" value="" >
					<input type="hidden" name="modeVisualiserSans" id="modeVisualiserSans" value="" >
					<input type="hidden" name="titrePageAvec" id="titrePageAvec" value="">
					<input type="hidden" name="titrePageSans" id="titrePageSans" value="">
					
				</form>
			
				<form enctype="multipart/form-data" method="post" action="<?php echo $GLOBALS['urlCarte'];?>" name="visualiser" id="formVisualiser"  target="_blank">
					<input type="hidden" name="origine" value="gpx2tdm" >
					<input type="hidden" name="idAffiche" value="<?php echo $_SESSION['idAffiche']; ?>" >
					<input type="hidden" name="profilVitesse" value="<?php echo $_SESSION['profilVitesse']; ?>" >
					<input type="hidden" name="modeVisualiser" id="modeVisualiser" value="portrait" >
					<input type="hidden" name="etiquette" id="etiquette" value="">
					<input type="hidden" name="titrePage" id="titrePage" value="">
					<input type="hidden" name="xml" id="xml" value='<?php echo(stripslashes($xml)); ?>' >
				</form>

				<form enctype="multipart/form-data" method="post" action="util/ficheRandoHtml.php" name="imprimeFicheHtml" target="_blank">
					<input type="hidden" name="idAffiche" value="<?php echo $_SESSION['idAffiche']; ?>" >
					<input type="hidden" name="profilVitesse" value="<?php echo $_SESSION['profilVitesse']; ?>" >
				</form>

				<form enctype="multipart/form-data" method="post" action="util/ficheRandoPdf.php" name="imprimeFichePdf" target="_blank">
					<input type="hidden" name="idAffiche" value="<?php echo $_SESSION['idAffiche']; ?>" >
					<input type="hidden" name="profilVitesse" value="<?php echo $_SESSION['profilVitesse']; ?>" >
				</form>

				<form id="formMenu" enctype="multipart/form-data" method="post" action="<?php if (isset($_GET['CPC'])) echo("gpx2tdm.php?CPC=OK"); else echo("gpx2tdm.php"); ?>" name="agir" target="_self">
					<input id="newAction" type="hidden" name="newAction" value="recalculer" >
					<input type="hidden" name="xml" value="" >
					<input type="hidden" name="idAffiche" value="<?php echo $_SESSION['idAffiche']; ?>" >
					<input type="hidden" name="profilVitesse" value="<?php echo $_SESSION['profilVitesse']; ?>" >
					<input type="hidden" name="modeAgir" value="portrait" >
					<input type="hidden" name="etiquetteJS" id="etiquetteJS" value="">

					
					<input type="hidden" name="ficheItiXmlSans" id="ficheItiXmlSans" value="<?php echo stripslashes($_SESSION['ficheItiXmlSans']); ?>" >
					<input type="hidden" name="ficheItiXmlAvec" id="ficheItiXmlAvec" value='<?php echo stripslashes($_SESSION['ficheItiXmlAvec']); ?>' >
					<input type="hidden" name="ficheItiModeVisualiserSans" id="ficheItiModeVisualiserSans" value="<?php echo $_SESSION['ficheItiModeVisualiserSans']; ?>" >
					<input type="hidden" name="ficheItiModeVisualiserAvec" id="ficheItiModeVisualiserAvec" value="<?php echo $_SESSION['ficheItiModeVisualiserAvec']; ?>" >
					<input type="hidden" name="ficheItiTitrePageSans" id="ficheItiTitrePageSans" value="<?php echo $_SESSION['ficheItiTitrePageSans']; ?>">
					<input type="hidden" name="ficheItiTitrePageAvec" id="ficheItiTitrePageAvec" value="<?php echo $_SESSION['ficheItiTitrePageAvec']; ?>">
					
					
			<?php
				}
				else {
			?>
				style = "overflow:scroll; padding:10px;"
				
				>
			<?php
				}
			?>

					<?php
					if ($action!="imprimerTdm")
					{
					?>
<div id="divMenu" style="margin: auto;">
	<h1
	style="margin-top: 10px; margin-bottom: 10px; vertical-align: middle; float: left;"><a href="http://gpx2tdm.free.fr/CeCILL/" title="application sous licence libre CeCILL : téléchargement" target="_blank" >gpx2tdm &nbsp; &nbsp;</a></h1>
	
<ul id="dd">
	<li style="z-index: 1010;">TDM
		<ul>
			<li onclick="confirmerSansDate();" title="<?php echo($GLOBALS['txt']['enregistreTdm']['title']); ?>"><?php echo($GLOBALS['txt']['enregistreTdm']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='imprimerTdm'; document.getElementById('formMenu').target='_blank'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['imprimeTdm']['title']); ?>"><?php echo($GLOBALS['txt']['imprimeTdm']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='envoyerTdmCSV'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); "  title="<?php echo($GLOBALS['txt']['envoyerTdmCsv']['title']); ?>"><?php echo($GLOBALS['txt']['envoyerTdmCsv']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='recalculer'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit();" title="<?php echo($GLOBALS['txt']['recalcul']['title']); ?>"><?php echo($GLOBALS['txt']['recalcul']['texte']); ?></li>
			
			<li onclick="confirmerNouveau(); " title="<?php echo($GLOBALS['txt']['quitter']['title']); ?>"><?php echo($GLOBALS['txt']['quitter']['texte']); ?></li>
		</ul>
	</li>
	<li style="z-index: 1009;">GPX
		<ul>
			<li onclick="document.getElementById('formMenu').newAction.value='creerTrk'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['creeWptTrk']['title']); ?>"><?php echo($GLOBALS['txt']['creeWptTrk']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='creerWpt'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['creeWpt']['title']); ?>"><?php echo($GLOBALS['txt']['creeWpt']['texte']); ?></li>
<?php	
if ($_SESSION['calcDeniv']== "trace" && $_SESSION['distanceLissage']!=0) {
?>
			<li onclick="document.getElementById('formMenu').newAction.value='creerTrkLisse'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['creeWptTrkLisse']['title']); ?>"><?php echo($GLOBALS['txt']['creeWptTrkLisse']['texte']); ?></li>
<?php
}
?>			
			<li onclick="document.getElementById('formMenu').newAction.value='analyser'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit();" title="<?php echo($GLOBALS['txt']['analyse']['title']); ?>"><?php echo($GLOBALS['txt']['analyse']['texte']); ?></li>
		</ul>
	</li>
	<li style="z-index: 1008;">Fiche-Rando
		<ul>
			<li onclick="document.getElementById('formMenu').newAction.value='imprimeFicheHtml'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['ficheHtml']['title']); ?>"><?php echo($GLOBALS['txt']['ficheHtml']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='imprimeFichePdf'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['fichePdf']['title']); ?>"><?php echo($GLOBALS['txt']['fichePdf']['texte']); ?></li>
		</ul>
	</li>
	<li style="z-index: 1007;">Carte
		<ul>
			<li onclick="document.getElementById('formMenu').newAction.value='visualiser'; document.getElementById('formMenu').modeAgir.value='auto'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['carteAuto']['title']); ?>"><?php echo($GLOBALS['txt']['carteAuto']['texte']); ?></li>

			<li onclick="document.getElementById('formMenu').newAction.value='visualiser'; document.getElementById('formMenu').modeAgir.value='portrait'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['cartePortrait']['title']); ?>"><?php echo($GLOBALS['txt']['cartePortrait']['texte']); ?></li>
			
			<li onclick="document.getElementById('formMenu').newAction.value='visualiser'; 
         document.getElementById('formMenu').modeAgir.value='paysage'; document.getElementById('formMenu').target='_self'; document.getElementById('formMenu').submit(); " title="<?php echo($GLOBALS['txt']['cartePaysage']['title']); ?>"><?php echo($GLOBALS['txt']['cartePaysage']['texte']); ?></li>
			
		</ul>
	</li>
</ul>
	
</div>
					<div id="onglets">
						<div id="onglettdm" class="onglet" title="<?php echo $GLOBALS['txt']['ongletTdm']['title']; ?>"
						onclick="afficher('tdm');  var formulaire=document.getElementById('formMenu'); formulaire.newAction.value='recalculer'; formulaire.target='_self';
						formulaire.idAffiche.value='tdm';
						formulaire.submit();"><?php echo $GLOBALS['txt']['ongletTdm']['texte']; ?></div>
						
						<div id="ongletprofil" class="onglet" title="<?php echo $GLOBALS['txt']['ongletProfil']['title']; ?>"
						onclick="afficher('profil');"><?php echo $GLOBALS['txt']['ongletProfil']['texte']; ?></div>
						
						<div id="ongletficheRando" class="onglet" title="<?php echo $GLOBALS['txt']['ongletFiche']['title']; ?>"
						onclick="afficher('ficheRando'); var formulaire=document.getElementById('formMenu'); formulaire.newAction.value='recalculer'; formulaire.target='_self';
						formulaire.idAffiche.value='ficheRando';
						formulaire.submit();"><?php echo $GLOBALS['txt']['ongletFiche']['texte']; ?></div>

					<div id="ongletaide" class="onglet" title="<?php echo $GLOBALS['txt']['ongletAide']['title']; ?>"
						onclick="afficher('aide');"><?php echo $GLOBALS['txt']['ongletAide']['texte']; ?></div>
					</div>


				
					<div id="profilVitesse"
					style="display: <?php if (	$_SESSION['profilVitesse']=="off") echo 'none'; else echo 'inline';?>;" onClick="this.style.display= 'none'; updateProfilVitesse('off');">
							<img src="util/profilVitesse.php?SESSION_NAME=<?php echo session_name(); ?>" title="Cliquez pour fermer"/>
					</div>
					</div>
					<?php
					}
					?>

					<div id="tdm"
					style="display: <?php if ($action=="imprimerTdm") echo "inline; position: static;"; else { if ($_SESSION['idAffiche']=="tdm") echo "block;"; else echo "none;";} ?>">
						<?php
							if ($action!="imprimerTdm") {
						?>
						<?php
						}
						?>
						<table  border="1" cellpadding="2" cellspacing="2">
							<tbody>
								<tr>
									<td style="text-align: left;" title="<?php echo($GLOBALS['txt']['nomRando']['title']); ?>">
									<?php echo($GLOBALS['txt']['nomRando']['texte']); ?>&nbsp;:
									<?php
									if ($action!="imprimerTdm")
									{
									?>

										<input name="nomRando" id="nomRando" size=40 value="<?php echo $_SESSION['nomRando'];?>">
									<?php
									}
									else echo(stripslashes($_SESSION['nomRando']));
									?>

									</td>

									<td  rowspan=2  style="text-align: left;" title="<?php echo($GLOBALS['txt']['vitesse']['title']); ?>"><?php echo($GLOBALS['txt']['vitesse']['texte']); ?>&nbsp;:
										<?php
											if ($action!="imprimerTdm")
											{
											?>
												<input id="vitesse" name="vitesse" size=4 style="text-align:
												center;" value="<?php echo $_SESSION['vitesse'];?>"
												
												onChange="
												if (document.getElementsByName('methode')[0].value=='profil') {
													document.getElementById('profilVitesse').style.display='inline';  updateProfilVitesse('on');

												} 
												this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();">
										<?php
										}
											else echo $_SESSION['vitesse'];
										?>
									</td>

									<?php
										if ($action!="imprimerTdm")
										{
									?>
									<?php
											if ((METRE_EFFORT_AFFICHEE==FALSE)&&(PROFIL_VITESSE_AFFICHEE==FALSE)) {
									?>
									<td style="text-align: left;" rowspan=2>
										<input type="hidden" name="methode" value="effort">
										<input type="hidden" name="coefPos" value="10">
										<input type="hidden" name="coefNeg" value="0">
										<?php echo(METHODE_TEXTE);?>
									</td>
									<?php 
											}
											else {
									?>
									<td style="text-align: left;">
									
										<span title="<?php echo($GLOBALS['txt']['methodeEffort']['title']);?> " >
											<input type="radio" class="radio" 
											name="methode"  value="effort" <?php if
											($_SESSION['methode']=="effort") echo 'checked="checked"'; ?>
											onClick="if (this.checked==true)
											{document.getElementById('spanCoef').style.display='inline';
											document.getElementById('spanK').style.display='none';
											document.getElementById('profilVitesse').style.display='none'; updateProfilVitesse('off');
											this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();
											}"
											>

											<?php echo($GLOBALS['txt']['methodeEffort']['texte']);?>
										</span>
										<span id="spanCoef" style="display: <?php if ($_SESSION['methode']=='effort') echo 'inline'; else echo 'none'; ?> ;">
											&nbsp;:&nbsp;
											<span title="<?php echo($GLOBALS['txt']['coefPos']['title']); ?>">
												<?php echo($GLOBALS['txt']['coefPos']['texte']); ?>
												<?php
													if ($action!="imprimerTdm")
													{
													?>
														<select name="coefPos"
														onChange="
														document.getElementById('profilVitesse').style.display='none'; updateProfilVitesse('off');
														this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();"
														>
															<?php
																for ($k=0; $k<=12; $k=$k+1) {
																	echo("<option value='$k'");
																	if ($k==$_SESSION['coefPos']) echo( ' selected="selected">');
																	else echo(">");
																	echo($k."</option>");
																}
															?>
														</select>

													<?php
													}
													else echo $_SESSION['coefPos'];
												?>
											</span>
											<span title="<?php echo($GLOBALS['txt']['coefNeg']['title']); ?>">
												<?php echo($GLOBALS['txt']['coefNeg']['texte']); ?>
													<?php
														if ($action!="imprimerTdm")
														{
													?>
														<select name="coefNeg"
														onChange="
														document.getElementById('profilVitesse').style.display='none'; updateProfilVitesse('off');
														this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();"
														>
															<?php
																for ($k=-5; $k<=10; $k=$k+1) {
																	echo("<option value='$k'");
																	if ($k==$_SESSION['coefNeg']) echo( ' selected="selected">');
																	else echo(">");
																	echo($k."</option>");
																}
															?>
														</select>
														<?php
														}
														else echo $_SESSION['coefNeg'];
														?>
											</span>
										</span>
									</td>
									<?php
											} // fin 
									} // fin !imprimerTdm
									else { // imprimerTdm
									?>
									<td style="text-align: left;" rowspan="2">
									<?php
										if ($_SESSION['methode']=='effort') {
											echo($GLOBALS['txt']['methodeEffort']['texte']." : ".$GLOBALS['txt']['coefPos']['texte'].":".$_SESSION['coefPos']." ; ".$GLOBALS['txt']['coefNeg']['texte'].":".$_SESSION['coefNeg']);
										}
										else {
											echo($GLOBALS['txt']['methodeProfil']['texte']." : ".$GLOBALS['txt']['kPos']['texte'].":".$_SESSION['kPos']." ; ".$GLOBALS['txt']['kNeg']['texte'].":".$_SESSION['kNeg']);
										}
									?>
									</td>
									
									
									
									
									<?php	
									}
									?>
								</tr>
								<tr>
									<td style="text-align: left; vertical-align: middle;" ><?php echo($GLOBALS['txt']['date']['texte']); ?>&nbsp;:
										<span title="<?php echo($GLOBALS['txt']['date']['title']); ?>">
										<?php
/*
											$date = $_SESSION['date'];
											$tab_date = explode("/",$date);
											$jour = $tab_date[0];
											$mois = $tab_date[1];
											$an = $tab_date[2];
											$dateValue = "$an-$mois-$jour";
*/
											$dateValue = internationaliserDate($_SESSION['date']);
										?>
											<?php
											if ($action!="imprimerTdm")
											{
											?>

												<input type="date"  id="inputDate" name="inputDate" value="<?php echo $dateValue; ?>" style="height: 12px;" autofocus/>
											<?php

												
											}
											else echo $_SESSION['date'];
											?>
										&nbsp;&nbsp;</span>
										
										<span title="<?php echo($GLOBALS['txt']['animateur']['title']); ?>">
											<?php echo($GLOBALS['txt']['animateur']['texte']); ?>&nbsp;:
									<?php
													if (UN_ANIMATEUR) { // pas club IRLPT ou CPC
											?>
										
											<?php
											if ($action!="imprimerTdm")
											{
											?>
												<input name="animateur" size=30 value="<?php echo stripslashes($_SESSION['animateur']);?>">
											<?php
												}
												else echo stripslashes($_SESSION['animateur']);
											?>
										</span>
									<?php
													}
													else { // club IRLPT ou CPC
														if ($action=="imprimerTdm") {
																	if ($_SESSION['animateur']!="")echo stripslashes($_SESSION['animateur']); 
																	else echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
														}
														else { // pas imprimer
															// récupérer les événtuels animateurs
															$tabAnimateurs = explode(", ", stripslashes($_SESSION['animateur']));
															if (!isset($tabAnimateurs[1])) $tabAnimateurs[1]=""; 
															// if ($tabAnimateurs[0]!="")
															// créer le champ animateur caché
															// créer les 2 selects vid
									?>
												<input id="animateur" name="animateur" size=30 value="" type="hidden">
												<select id="selAnimateur1" name="selAnimateur1"  onChange="actualiserAnimateur();">
									<?php
										foreach ($GLOBALS['animateur'] AS $unNom) {
											echo("<option value='$unNom' ");
											if ($tabAnimateurs[0]==$unNom) echo(" selected='selected'");                              
											echo(">$unNom</option>");
										}
									?>
												</select>&nbsp;
												<select id="selAnimateur2" name="selAnimateur2" onChange="actualiserAnimateur();"">
									<?php
										foreach ($GLOBALS['animateur'] AS $unNom) {
											echo("<option value='$unNom' ");
											if ($tabAnimateurs[1]==$unNom) echo(" selected='selected'");                              
											echo(">$unNom</option>");
										}
									?>
												</select>
								<?php
														}
													}
											?>
									
									</td>

									<?php
										if ($action!="imprimerTdm")
										{
										?>

								<?php if (PROFIL_VITESSE_AFFICHEE) {?>
									<td style="text-align: left;"	>
										

										<span title="<?php echo($GLOBALS['txt']['methodeProfil']['title']); ?>"
										>
											<input type="radio" class="radio" 
											name="methode" value="profil" <?php if
											($_SESSION['methode']=="profil") echo 'checked="checked"'; ?>
											onClick="if (this.checked==true)
											{document.getElementById('spanCoef').style.display='none';
											document.getElementById('spanK').style.display='inline';
											document.getElementById('profilVitesse').style.display='inline';
											updateProfilVitesse('on'); this.form.newAction.value='recalculer';
											this.form.target='_self'; this.form.submit();
											}"
											>
											<?php echo($GLOBALS['txt']['methodeProfil']['texte']);?>

										</span>
										<span id="spanK" style="display: <?php if ($_SESSION['methode']=='profil') echo 'inline'; else echo 'none'; ?> ;>&nbsp;:&nbsp;
											<span  title="<?php echo($GLOBALS['txt']['kPos']['title']); ?>">
												<?php echo($GLOBALS['txt']['kPos']['texte']); ?>
												<?php
													if ($action!="imprimerTdm")
													{
													?>
												<select id="kPos" name="kPos" onChange="
												document.getElementById('profilVitesse').style.display='inline'; updateProfilVitesse('on');
												this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();"
												onClick="document.getElementById('profilVitesse').style.display='inline'; updateProfilVitesse('on');"
												>
													<?php
														for ($k=0.1; $k<=2; $k=$k+0.1) {
															echo("<option value='$k'");
															$epsilon = 0.01;
															if (abs($k-$_SESSION['kPos'])<$epsilon) echo( ' selected="selected">');
															else echo(">");
															echo($k."</option>");
														}
													?>
												</select>

													<?php
													}
													else echo $_SESSION['kPos'];
												?>
											</span>
											<span title="<?php echo($GLOBALS['txt']['kNeg']['title']); ?>">
												<?php echo($GLOBALS['txt']['kNeg']['texte']); ?>
													<?php
														if ($action!="imprimerTdm")
														{
													?>
														<select id="kNeg" name="kNeg"
														onChange="
														document.getElementById('profilVitesse').style.display='inline'; updateProfilVitesse('on');
														this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();"
														onClick="document.getElementById('profilVitesse').style.display='inline'; updateProfilVitesse('on');"
														>
															<?php
																for ($k=0.1; $k<=2; $k=$k+0.1) {
																	echo("<option value='$k'");
																	$epsilon = 0.01;
																	if (abs($k-$_SESSION['kNeg'])<$epsilon) echo( ' selected="selected">');
																	else echo(">");
																	echo($k."</option>");
																}
															?>
														</select>
														<?php
														}
														else echo $_SESSION['kNeg'];
														?>
											</span>
										</span>

									</td>
									<?php } ?>
								<?php } ?>

								</tr>

								<tr>
									<td colspan="3">
									<?php
									if ($action!="imprimerTdm")
									{
									?>
										<?php echo($GLOBALS['txt']['calcDeniv']['texte']);?>
										<span title="<?php echo($GLOBALS['txt']['pointsPassage']['title']);?>" >
										<input type="radio" name="calcDeniv" value="passage" <?php if ($_SESSION['calcDeniv']== "passage") echo ('checked="checked"');?> onClick="document.getElementById('spanOptions').style.display='none'; this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();" />
											<?php echo(count($_SESSION['pdp'])." ".$GLOBALS['txt']['pointsPassage']['texte']);?>
										</span>
									<?php
									}
									else {
										if ($_SESSION['calcDeniv']== "passage") {
											echo($GLOBALS['txt']['calcDeniv']['texte']." ".count($_SESSION['pdp'])." ".$GLOBALS['txt']['pointsPassage']['texte']);
										}
									?>

									<?php
									}
									?>
									<?php
									if ($action!="imprimerTdm")
									{
									?>
									<span title="<?php echo($GLOBALS['txt']['pointsTrace']['title']);?>" 
									<?php //if ($GLOBALS['clients']=="IRLPT") echo('style="display:none;"'); ?>
									>
										<input type="radio" name="calcDeniv" value="trace" <?php if (	$_SESSION['calcDeniv']== "trace" ) echo ('checked="checked"');?>  onClick="document.getElementById('spanOptions').style.display='inline'; this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();"/>
											<?php echo(count($_SESSION['trk'])." ".$GLOBALS['txt']['pointsTrace']['texte']);?>
									</span>
									<?php
									}
									else {
										if ($_SESSION['calcDeniv']== "trace") {
											echo($GLOBALS['txt']['calcDeniv']['texte']." ".count($_SESSION['trk'])." ".$GLOBALS['txt']['pointsTrace']['texte']);
										}
									}
									?>

										<span id="spanOptions" style="display:  <?php if (	$_SESSION['calcDeniv']== "passage") echo ('none'); else echo('inline');?> ">
											&nbsp; &nbsp;
											<span style=" font-style: italic; font-size: x-small;" title="<?php echo($GLOBALS['txt']['distanceLissage']['title']); ?>"><?php echo($GLOBALS['txt']['distanceLissage']['texte']); ?>&nbsp;:
											<?php if ($action!="imprimerTdm") { ?>
												<select name="distanceLissage" onchange="this.form.newAction.value='recalculer';
												this.form.target='_self'; this.form.submit();">
													<option value="0" <?php if ($_SESSION['distanceLissage']==0) echo('selected="selected"');?>>pas de lissage</option>
													<option value="10" <?php if ($_SESSION['distanceLissage']==10) echo('selected="selected"');?>>moyenne sur 10m</option>
													<option value="20" <?php if ($_SESSION['distanceLissage']==20) echo('selected="selected"');?>>moyenne sur 20m</option>
													<option value="30" <?php if ($_SESSION['distanceLissage']==30) echo('selected="selected"');?>>moyenne sur 30m</option>
													<option value="40" <?php if ($_SESSION['distanceLissage']==40) echo('selected="selected"');?>>moyenne sur 40m</option>
													<option value="50" <?php if ($_SESSION['distanceLissage']==50) echo('selected="selected"');?>>moyenne sur 50m</option>
													<option value="60" <?php if ($_SESSION['distanceLissage']==60) echo('selected="selected"');?>>moyenne sur 60m</option>
													<option value="70" <?php if ($_SESSION['distanceLissage']==70) echo('selected="selected"');?>>moyenne sur 70m</option>
													<option value="80" <?php if ($_SESSION['distanceLissage']==80) echo('selected="selected"');?>>moyenne sur 80m</option>
													<option value="90" <?php if ($_SESSION['distanceLissage']==90) echo('selected="selected"');?>>moyenne sur 90m</option>
													<option value="100" <?php if ($_SESSION['distanceLissage']==100) echo('selected="selected"');?>>moyenne sur 100m</option>
												</select>
											<?php }
											else {
													if ($_SESSION['distanceLissage']==0) echo("pas de lissage");
													else echo("moyenne sur ".$_SESSION['distanceLissage'].' mètres');
												} ?>
											</span>
											&nbsp; &nbsp;
											<span style=" font-style: italic; font-size: x-small;" title="<?php echo($GLOBALS['txt']['seuil']['title']); ?>"><?php echo($GLOBALS['txt']['seuil']['texte']); ?>&nbsp;:
											<?php if ($action!="imprimerTdm") { ?>
												<select name="seuil" onchange="this.form.newAction.value='recalculer';
												this.form.target='_self'; this.form.submit();">
													<option value="0" <?php if ($_SESSION['seuil']==0) echo('selected="selected"');?>>pas de seuil</option>
													<option value="2" <?php if ($_SESSION['seuil']==2) echo('selected="selected"');?>>2 m</option>
													<option value="5" <?php if ($_SESSION['seuil']==5) echo('selected="selected"');?>>5 m</option>
													<option value="10" <?php if ($_SESSION['seuil']==10) echo('selected="selected"');?>>10 m</option>
													<option value="15" <?php if ($_SESSION['seuil']==15) echo('selected="selected"');?>>15 m</option>
													<option value="20" <?php if ($_SESSION['seuil']==20) echo('selected="selected"');?>>20 m</option>
													<option value="30" <?php if ($_SESSION['seuil']==30) echo('selected="selected"');?>>30 m</option>
													<option value="40" <?php if ($_SESSION['seuil']==40) echo('selected="selected"');?>>40 m</option>
													<option value="50" <?php if ($_SESSION['seuil']==50) echo('selected="selected"');?>>50 m</option>
												</select>
											<?php }
											else {
												if ($_SESSION['seuil']==0) echo("pas de seuil");
												else echo($_SESSION['seuil']. " m");
											} ?>
											</span>
										</span>

										</td>
									</tr>
									
									
							</tbody>
						</table>

						<table style="text-align: left; margin-top: 5px;" border="1" cellpadding="2" cellspacing="2" >
							<tbody>
								<tr>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['de']['title']); ?>"><?php echo($GLOBALS['txt']['de']['texte']); ?><br></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['a']['title']); ?>"><?php echo($GLOBALS['txt']['a']['texte']); ?> <br></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['name']['title']); ?>"><?php echo($GLOBALS['txt']['name']['texte']); ?><br>
									</td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['utm']['title']); ?>"><?php echo($GLOBALS['txt']['utm']['texte']); ?><br></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['azimut']['title']); ?>"><?php echo($GLOBALS['txt']['azimut']['texte']); ?><br></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['ele']['title']); ?>"><?php echo($GLOBALS['txt']['ele']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['distance']['title']); ?>"><?php echo($GLOBALS['txt']['distance']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['distanceCumul']['title']); ?>"><?php echo($GLOBALS['txt']['distanceCumul']['texte']); ?></td>
									<?php if ($_SESSION['calcDeniv']=="passage") { ?>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['deniv']['title']); ?>"><?php echo($GLOBALS['txt']['deniv']['texte']); ?><br></td>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['pente']['title']); ?>"><?php echo($GLOBALS['txt']['pente']['texte']); ?></td>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['denivAdd']['title']); ?>"><?php echo($GLOBALS['txt']['denivAdd']['texte']); ?></td>
									<?php }
									else { ?>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['denivPos']['title']); ?>"><?php echo($GLOBALS['txt']['denivPos']['texte']); ?><br></td>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['denivNeg']['title']); ?>"><?php echo($GLOBALS['txt']['denivNeg']['texte']); ?><br></td>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['pentePos']['title']); ?>"><?php echo($GLOBALS['txt']['pentePos']['texte']); ?><br></td>
										<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['penteNeg']['title']); ?>"><?php echo($GLOBALS['txt']['penteNeg']['texte']); ?><br></td>

									<?php } ?>

									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['dureeAdd']['title']); ?>"><?php echo($GLOBALS['txt']['dureeAdd']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;"  title="<?php echo($GLOBALS['txt']['duree']['title']); ?>"><?php echo($GLOBALS['txt']['duree']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['pause']['title']); ?>"><?php echo($GLOBALS['txt']['pause']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['heureArrivee']['title']); ?>"><?php echo($GLOBALS['txt']['heureArrivee']['texte']); ?></td>
									<td style="vertical-align: middle; text-align: center;" title="<?php echo($GLOBALS['txt']['observ']['title']); ?>"><?php echo($GLOBALS['txt']['observ']['texte']); ?></td>
								</tr>

								<?php
								foreach($_SESSION['pdp'] as $i =>$pdp) {
									$de = chr(65+($i-1)%26);
									if ($i>26) $de = $de . floor($i/26);
									$a = chr(65+$i%26);
									if ($i+1>26) $a = $a . floor(($i+1)/26);
								?>
								<tr>
									<td><?php if ($i>0) echo $GLOBALS['txt']['de']['texte'],"&nbsp;",$de; else echo " "; ?>
									</td>
									<td><?php if ($i>0) echo $GLOBALS['txt']['a']['texte'],"&nbsp;"; echo $a,"&nbsp;: ";  ?>
									</td>
									<td style="text-align: left;"><?php if ($action!="imprimerTdm") echo "<input name=\"name[",$i,"]\" style=\"width:97%; \" value=\"",stripslashes($pdp['name']),"\">"; else echo  stripslashes($pdp['name']); ?></td>
									<td style="text-align: center; font-size: xx-small"><?php
										$format1= '%1$d%2$s&nbsp;%3$08.3F';
										$format2= 'UTM&nbsp;%1$08.3F';
										printf($format1, $pdp['utm']['zone'], $pdp['utm']['lettre'], $pdp['utm']['Est']);
										echo "<br>";
										printf($format2, $pdp['utm']['Nord']);
									?>
									</td>
	<!-- Attention ; on travaille sur le point de passage de fin du tronçon et on a besoin de l'azimut du point de passage du début du tronçon donc il faut prendre l'azimmut du point de passage ['pdp'][$i-1]['azimut']-->
									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo round($_SESSION['pdp'][$i-1]['azimut']),"&nbsp;"; ?>
									</td>
									<td style="text-align: right;">
										<?php
										if ($action!="imprimerTdm")
										{
										?>
										<input name="ele[<?php echo $i;?>]" size=4 style="text-align: right;" value="<?php echo ($pdp['ele']);?>">
										<?php } else echo $pdp['ele'],"&nbsp;"; ?>
									</td>
									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo (round($pdp['distance'])."&nbsp;"); ?>
									</td>
									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo (round($pdp['distanceCumul'])."&nbsp;"); ?>
									</td>
									<?php if ($_SESSION['calcDeniv']=="passage") { ?>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo ($pdp['deniv']."&nbsp;"); ?>
										</td>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo (round($pdp['pente']*100)."% "); ?>
										</td>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>">
											<?php if ($i>0) if ($action!="imprimerTdm") echo "<input name=\"denivAdd[",$i,"]\" size=2 style=\"text-align: right;\" value=\"",$pdp['denivAdd'],"\">"; else echo $pdp['denivAdd'],"&nbsp;"; else echo "";?>
										</td>
									<?php }
									else { ?>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo ($pdp['denivPos']."&nbsp;"); ?>
										</td>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo ($pdp['denivNeg']."&nbsp;"); ?>
										</td>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo (round($pdp['pentePos']*100)."% "); ?>
										</td>
										<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo (round($pdp['penteNeg']*100)."% "); ?>
										</td>
									<?php } ?>

									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>">
										<?php if ($i>0) if ($action!="imprimerTdm") echo "<input name=\"dureeAdd[",$i,"]\" size=2 style=\"text-align: right;\" value=\"",$pdp['dureeAdd'],"\">"; else echo $pdp['dureeAdd'],"&nbsp;"; else echo "";?>
									</td>
									</td>
									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>"><?php if ($i>0) echo(round($pdp['duree'])."&nbsp;"); ?>
									</td>
									<td style="text-align: right;<?php if ($i==0) echo("background-color: rgba(204, 204, 204, 0.5);") ?>">
										<?php if ($i>0) if ($action!="imprimerTdm") echo "<input name=\"pause[",$i,"]\" size=2 style=\"text-align: right;\" value=\"",$pdp['pause'],"\">"; else echo $pdp['pause'],"&nbsp;"; else echo ""; ?>
									</td>
									<td style="text-align: center;">
										<?php
											$h = floor($pdp['heure']/60);
											$m = $pdp['heure']%60;
											if ($h<10) $h = "0" . $h;
											if ($m<10) $m = "0" . $m;
										?>
										<?php
											if ($action!="imprimerTdm" and $i==0)
											{
										?>
										<input id="heureArrivee" name="heureArrivee" title="heure d'arrivée au point de départ de la rando ; cliquez pour modifier cette heure" value="<?php echo $h,":",$m; ?>"
										size="3" type="text" readonly="readonly" onClick="ouvrirHeureMinute();" style="	cursor: default;">
										<div id="heureMinute"
										style="position: fixed; font-size: 8px; font-family: sans-serif; width: 200px;  color: white; background-color: #663366; padding: 5px; cursor: default; display: none; " onClick="this.style.display='none';">

												<table id="tableHM"
												style="text-align: center; vertical-align: middle; empty-cells: show;
												border-collapse: collapse; width: 100%; background-color: #CC66CC; border-color: black;"
												border="1" cellpadding="3" cellspacing="2">
												<tbody>
												<tr>
													<td colspan="4" style="border-color: black;">heure<br>
													</td>
													<td colspan="2" style="border-color: black;">minute<br>
													</td>
												</tr>
												<tr>
													<td id="tdH0" class="tdNonSelect" onClick="changerHeure(0);">00
													</td>
													<td id="tdH1" class="tdNonSelect" onClick="changerHeure(1);">01
													</td>
													<td id="tdH2" class="tdNonSelect" onClick="changerHeure(2);">02
													</td>
													<td id="tdH3" class="tdNonSelect" onClick="changerHeure(3);">03
													</td>
													<td id="tdM0" class="tdNonSelect" onClick="changerMinute(0);">00
													</td>
													<td id="tdM1" class="tdNonSelect" onClick="changerMinute(1);">05
													</td>
												</tr>
												<tr>
													<td id="tdH4" class="tdNonSelect" onClick="changerHeure(4);">04
													</td>
													<td id="tdH5" class="tdNonSelect" onClick="changerHeure(5);">05
													</td>
													<td id="tdH6" class="tdNonSelect" onClick="changerHeure(6);">06
													</td>
													<td id="tdH7" class="tdNonSelect" onClick="changerHeure(7);">07
													</td>
													<td id="tdM2" class="tdNonSelect" onClick="changerMinute(2);">10
													</td>
													<td id="tdM3" class="tdNonSelect" onClick="changerMinute(3);">15
													</td>
												</tr>
												<tr>
													<td id="tdH8" class="tdNonSelect" onClick="changerHeure(8);">08
													</td>
													<td id="tdH9" class="tdNonSelect" onClick="changerHeure(9);">09
													</td>
													<td id="tdH10" class="tdNonSelect" onClick="changerHeure(10);">10
													</td>
													<td id="tdH11" class="tdNonSelect" onClick="changerHeure(11);">11
													</td>
													<td id="tdM4" class="tdNonSelect" onClick="changerMinute(4);">20
													</td>
													<td id="tdM5" class="tdNonSelect" onClick="changerMinute(5);">25
													</td>
												</tr>
												<tr>
													<td id="tdH12" class="tdNonSelect" onClick="changerHeure(12);">12
													</td>
													<td id="tdH13" class="tdNonSelect" onClick="changerHeure(13);">13
													</td>
													<td id="tdH14" class="tdNonSelect" onClick="changerHeure(14);">14
													</td>
													<td id="tdH15" class="tdNonSelect" onClick="changerHeure(15);">15
													</td>
													<td id="tdM6" class="tdNonSelect" onClick="changerMinute(6);">30
													</td>
													<td id="tdM7" class="tdNonSelect" onClick="changerMinute(7);">35
													</td>
												</tr>
												<tr>
													<td id="tdH16" class="tdNonSelect" onClick="changerHeure(16);">16
													</td>
													<td id="tdH17" class="tdNonSelect" onClick="changerHeure(17);">17
													</td>
													<td id="tdH18" class="tdNonSelect" onClick="changerHeure(18);">18
													</td>
													<td id="tdH19" class="tdNonSelect" onClick="changerHeure(19);">19
													</td>
													<td id="tdM8" class="tdNonSelect" onClick="changerMinute(8);">40
													</td>
													<td id="tdM9" class="tdNonSelect" onClick="changerMinute(9);">45
													</td>
												</tr>
												<tr>
													<td id="tdH20" class="tdNonSelect" onClick="changerHeure(20);">20
													</td>
													<td id="tdH21" class="tdNonSelect" onClick="changerHeure(21);">21
													</td>
													<td id="tdH22" class="tdNonSelect" onClick="changerHeure(22);">22
													</td>
													<td id="tdH23" class="tdNonSelect" onClick="changerHeure(23);">23
													</td>
													<td id="tdM10" class="tdNonSelect" onClick="changerMinute(10);">50
													</td>
													<td id="tdM11" class="tdNonSelect" onClick="changerMinute(11);">55
													</td>
												</tr>
												</tbody>
												</table>
											
										</div>
										<?php
											}

											else {
												echo $h,":",$m;
											}
										?>
									</td>
									<td>
										<?php 
										if (isset($pdp['observ'])) $affObserv = stripslashes($pdp['observ']);
										else $affObserv = "";
										if ($action!="imprimerTdm") echo "<input name=\"observ[",$i,"]\" style=\"width:97%; \" value=\"",$affObserv,"\">"; 
										else echo $affObserv;
										?>
									</td>
								</tr>

								<?php
								}
								?>

							</tbody>
						</table>

						<table style="text-align: left; margin-top:5px; font-weight: 500; font-size: small;" border="1" cellpadding="2"
						cellspacing="2">
							<tbody>
								<tr>
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['distanceTotale']['title']); ?>"><?php echo($GLOBALS['txt']['distanceTotale']['texte']); ?>&nbsp;:</td>
									<td style="text-align: center;"><?php echo $distanceCumul," km"; ?>
									</td>
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['dureeTotale']['title']); ?>"><?php echo($GLOBALS['txt']['dureeTotale']['texte']); ?>&nbsp;:
									</td>
									<td style="text-align: center;">
										<?php
											$h = floor($dureeCumul/60);
											$m = $dureeCumul%60;
											if ($m<10) $m = "0" . $m;
											echo $h,"h",$m;
										?>
										<br>
									</td>
									
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['denivTotPos']['title']); ?>"><?php echo($GLOBALS['txt']['denivTotPos']['texte']); ?>&nbsp;:
									</td>
									<td style="text-align: center;"><?php echo "+",$denivCumulPos," m"; ?>
									</td>
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['denivTotNeg']['title']); ?>"><?php echo($GLOBALS['txt']['denivTotNeg']['texte']); ?>&nbsp;:
									</td>
									<td style="text-align: center;"><?php echo $denivCumulNeg," m"; ?>
									
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['altitudeMin']['title']); ?>"><?php echo($GLOBALS['txt']['altitudeMin']['texte']); ?>&nbsp;:
									</td>
									<td style="text-align: center;"><?php echo $_SESSION['altitudeMin']," m"; ?>
									
									<td style="text-align: right;" title="<?php echo($GLOBALS['txt']['altitudeMax']['title']); ?>"><?php echo($GLOBALS['txt']['altitudeMax']['texte']); ?>&nbsp;:
									</td>
									<td style="text-align: center;"><?php echo $_SESSION['altitudeMax']," m"; ?>
									
								</tr>

							</tbody>
						</table>

						
						<br>
					</div> <!-- tdm style="page-break-inside: avoid;"-->
					<div id="profil"
					style="display: <?php if ($action=="imprimerTdm") echo "inline; position: static;"; else { if ($_SESSION['idAffiche']=="profil") echo "block;"; else echo "none;";} ?>">
						<?php
						if ($action!="imprimerTdm") {
						?>
						<?php
						}
						?>

							<img src="util/profil.php?SESSION_NAME=<?php echo session_name(); ?>" title="en rouge : profil des points de passage ; en vert : profil des points de trace"/>
					</div>
					<?php
						if ($action!="imprimerTdm") {
					?>
					<hr>

					<div id="ficheRando"
					style="display: <?php if ($action=="imprimerTdm") echo "none"; else { if ($_SESSION['idAffiche']=="ficheRando") echo "block"; else echo "none";} ?>;">
						<h4> paramètres de la fiche </h4>
						<table class="tableFiche" border="1" cellpadding="2" cellspacing="2">
							<tbody>

								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['logoUrl']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">


									<?php if (!$_SESSION['ficheLogoAucun']) { ?>
										<img src="data:image/<?php echo $_SESSION['ficheLogoImageType']; ?>;base64,<?php echo $_SESSION['ficheLogoImage']; ?>"/>
										<br>
									<?php }
									else echo "aucun logo <br>";
									
									// sélection du logo si pas club
									if (LOGO_REMPLACABLE) {	
									?>
										<select class="moyen" size="1" name="ficheLogoChoix" onChange="
									if (this.options[1].selected) document.getElementById('choixLogo').style.display='inline';
									else {
										document.getElementById('choixLogo').style.display='none';
										this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();
										if (this.options[0].selected) document.getElementById('mettreAJour').style.display='none';
										else document.getElementById('mettreAJour').style.display='inline';
									}
									">
											<option selected="selected" value="noChange">ne pas modifier</option>
											<option value="other">autre logo</option>
											<option value="default">logo par défaut</option>
											<option value="none">aucun logo</option>
										</select>

										<span id="choixLogo" style="display:none;">
											<?php echo $GLOBALS['txt']['ficheRando']['logoUrl']['autre'], " : " ?>
											<input  name="ficheLogoUrl" type="file" class="tdFicheControl" onChange="this.form.newAction.value='recalculer'; this.form.target='_self'; this.form.submit();">
										</span>
									<?php
									}
									?>
										
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['titre']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<?php echo $_SESSION['nomRando']; ?>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['titreFiche']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="normal" name="ficheTitreFiche" value="<?php echo $_SESSION['ficheTitreFiche']; ?>">
									</td>
								</tr>
								<tr <?php if(!SOUS_TITRE) echo('style = "display: none;"'); ?>>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['sousTitre']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="normal"  name="ficheSousTitre" 
										value="<?php if(!SOUS_TITRE) echo(""); else echo $_SESSION['ficheSousTitre']; ?>">
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['date']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<span id="ficheDate"></span>
										<?php
											if ($_SESSION['date']!="//") {
												$date = $_SESSION['date'];
												$tab_date = explode("/",$date);
												$jour = (int) $tab_date[0];
												$mois = (int) $tab_date[1];
												$an = (int) $tab_date[2];
												setlocale(LC_TIME, 'fr_FR.UTF8');
												$_SESSION['ficheDate'] = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
											}
											else {
												$_SESSION['ficheDate'] = "";
											}
											echo $_SESSION['ficheDate'];
										?>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['presentation']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="fichePresentation"  rows="5"><?php echo $_SESSION['fichePresentation']; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['remarques']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheRemarques"  rows="5"><?php if (isset($_SESSION['ficheRemarques'])) echo $_SESSION['ficheRemarques']; ?></textarea>
									</td>
								</tr>

	<?php
		if (THEME) { // spécifique CPC
	?>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['themeCode']['id']; ?>
									</td>
									<td  class="tdFicheValeur" >
												<select class="moyen"  id="ficheThemeCode" name="ficheThemeCode">
									<?php
										foreach ($GLOBALS['theme']['code'] AS $i => $unCode) {
											echo("<option value='$unCode'");
											if ($_SESSION['ficheThemeCode']==$unCode) echo(" selected='selected'");                              
											echo(">".$GLOBALS['theme']['intitule'][$i]."</option>");
										}
									?>
												</select>&nbsp;

									</td>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['themeDescription']['id']; ?>
									</td>
									<td  class="tdFicheValeur" >
												<input class="normal" style="width:99%;" id="ficheThemeDescription" name="ficheThemeDescription"  value="<?php echo $_SESSION['ficheThemeDescription']; ?>">
									</td>
							</tr>
	<?php
		}
	?>
								


								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['niveau']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
	<?php
		if (!NIVEAU_LISTE) { // pas club IRLPT
	?>
										<input class="reduit" name="ficheNiveau" value="<?php echo $_SESSION['ficheNiveau']; ?>" <?php //if ($GLOBALS['clients']!="CPC") echo(" disabled=\"disabled\""); ?>>
	<?php
		}
		else { // club IRLPT
			if ($_SESSION['ficheNiveau']=="") $_SESSION['ficheNiveau']="P1T1";
	?>
										<input id="ficheNiveau" name="ficheNiveau" type="hidden" value="<?php echo($_SESSION['ficheNiveau']);?>">
										
										<select  class="moyen" id="selNiveau" name="selNiveau"  onChange="actualiserNiveau();">
	<?php
										foreach ($GLOBALS['niveau'] AS $unNiveau) {
											echo("<option value='$unNiveau' ");
											if ($_SESSION['ficheNiveau']==$unNiveau) echo(" selected='selected'");                              
											echo(">$unNiveau</option>");
										}
	?>
										</select>&nbsp;
	<?php
		}
	?>
									</td>
								</tr>
	<?php
		if (IBP) {
	?>
								<tr>
									<td class="tdFicheTitre">
										<?php echo IBP_TXT; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="reduit" id="ficheIbpIndex" name="ficheIbpIndex"  value="<?php echo $_SESSION['ficheIbpIndex']; ?>" readonly="readonly">
	<?php
										echo IBP_COMMENT;
	?>
										
									</td>
									
								</tr>
<?php
		}
?>
								
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['denivPos']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<?php echo "+",$denivCumulPos," m" ?>
									</td>

									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['denivPosFiche']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										+ <input class="reduit" name="ficheDenivPosFiche" value="<?php echo $_SESSION['ficheDenivPosFiche']; ?>"> m
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['denivNeg']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<?php echo $denivCumulNeg," m"; ?>
									</td>

									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['denivNegFiche']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										&#x2212; <input class="reduit" name="ficheDenivNegFiche" value="<?php echo $_SESSION['ficheDenivNegFiche']; ?>"> m
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['longueur']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<?php echo $distanceCumul," km"; ?>
									</td>

									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['longueurFiche']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<input class="reduit" name="ficheLongueurFiche" value="<?php echo $_SESSION['ficheLongueurFiche']; ?>"> km
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['duree']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<?php
											$h = floor($dureeCumul/60);
											$m = $dureeCumul%60;
											if ($m<10) $m = "0" . $m;
											echo $h,"h",$m;
										?>
									</td>

									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['dureeFiche']['id']; ?>
									</td>
									<td  class="tdFicheValeur">
										<?php
											$h = floor($_SESSION['ficheDureeFiche']/60);
											$m = $_SESSION['ficheDureeFiche']%60;
											if ($m<10) $m = "0" . $m;
										?>
										<input class="reduit" name="ficheDureeFicheH" value="<?php echo $h; ?>"> h
										<input class="reduit" name="ficheDureeFicheM" value="<?php echo $m; ?>"> min
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['difficultes']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheDifficultes"  rows="3"><?php echo $_SESSION['ficheDifficultes']; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['carte']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="moyen" name="ficheCarte" value="<?php echo $_SESSION['ficheCarte']; ?>">
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['rdv']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheRDV"  rows="3"><?php echo $_SESSION['ficheRDV']; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['depart']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="moyen" name="ficheDepart" value="<?php echo $_SESSION['ficheDepart']; ?>">
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['parking']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<?php //if ($_SESSION['ficheParking']=="") $_SESSION['ficheParking']=$_SESSION['coordParking'];
										?>
										<textarea class="normal" name="ficheParking"  style="width:99.5%" rows="3"><?php echo $_SESSION['ficheParking']; ?></textarea>
									</td>
								</tr>

								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['trajet']['id']; ?>
										
									</td>
									<td  class="tdFicheValeur" colspan="3">

<?php
if (OPENSERVICE_ROUTE && AU_MOINS_56 ) {
?>
										<table style="width: 600px; font-size: small;">
											<tbody>
												<tr>
													<td>longitude départ :
													</td>
													<td><input class="coordIti" name="ficheItiDepartLon" id="ficheItiDepartLon" required="required" type="text" value="<?php echo($_SESSION['ficheItiDepartLon']); ?>">
													</td>
													<td rowspan="2">
														<button name="appel" type="button" value="ajouter itinéraire(s) openRoute Service" onclick="lancerOpenRoute(); ">ajouter itinéraire(s) openRoute Service
														</button>
													</td>
												</tr>
												<tr>
													<td>latitude départ : 
													</td> 
													<td><input class="coordIti" name="ficheItiDepartLat" id="ficheItiDepartLat" required="required" type="text" value="<?php echo($_SESSION['ficheItiDepartLat']); ?>">
													</td>
												</tr>
												<tr>
													<td>longitude arrivée :
													</td>
													<td><input class="coordIti" name="ficheItiArriveeLon" id="ficheItiArriveeLon" required="required" type="text" value="<?php echo($_SESSION['ficheItiArriveeLon']); ?>">
													</td>
													<td>
														<button type="button" style="display: <?php if ($_SESSION['ficheItiXmlSans']!="") echo("inline"); else echo("none"); ?>;" name="visu" id="visuSans" onclick="visualiserCarte('sansPeage');">voir l'itinéraire sans péage sur la carte
														</button>
													</td>
												</tr>
												<tr>
													<td>latitude arrivée :
													</td>
													<td><input class="coordIti" name="ficheItiArriveeLat" id="ficheItiArriveeLat" required="required" type="text" value="<?php echo($_SESSION['ficheItiArriveeLat']); ?>">
													</td>
													<td>
														<button type="button" style="display:  <?php if ($_SESSION['ficheItiXmlAvec']!="") echo("inline"); else echo("none"); ?>;" name="visu" id="visuAvec" onclick="visualiserCarte('avecPeage');">voir l'itinéraire avec péage sur la carte
														</button>
													</td>
												</tr>
											</tbody>
										</table>
<p>Les coordonnées par défaut du point de départ et du point d'arrivée de l'itinéraire routier sont respectivement les coordonnées du point de rendez-vous du club et du premier point de la trace de la randonnée. Elles peuvent être modifiées par exemple dans le cas des randonnées d'un séjour en étoile.<br>

OpenRoute Service propose un itinéraire sans péage (et un intinéraire avec péage dans le cas où l'itinéraire avec péage permet de gagner du temps).</p>
<?php
	}
?>

										<textarea class="normal" name="ficheTrajet" id="ficheTrajet" style="width:99.5%" rows="12"><?php echo htmlentities($_SESSION['ficheTrajet']); ?></textarea>
									</td>
								</tr>
								
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['trajetKm']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="reduit" name="ficheTrajetKm" id="ficheTrajetKm" value="<?php echo $_SESSION['ficheTrajetKm']; ?>"> km
									</td>
								</tr>

								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['covoiturage']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="normal" name="ficheCovoiturage" id="ficheCovoiturage" value="<?php echo $_SESSION['ficheCovoiturage']; ?>">
									</td>
								</tr>
								
								
								
								
								
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['laRandonnee']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheLaRandonnee" style="width:99.5%" rows="10"><?php echo $_SESSION['ficheLaRandonnee']; ?></textarea>
									</td>
								</tr>
								
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['equipement']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheEquipement" style="width:99.5%" rows="3"><?php echo $_SESSION['ficheEquipement']; ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['animateur']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input type="hidden" name="ficheAnimateur" value="<?php echo $_SESSION['ficheAnimateur']; ?>"><?php echo $_SESSION['ficheAnimateur']; ?>
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['complementsTitre']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<input class="normal" name="ficheComplementsTitre" value="<?php echo $_SESSION['ficheComplementsTitre']; ?>">
									</td>
								</tr>
								<tr>
									<td class="tdFicheTitre">
										<?php echo $GLOBALS['txt']['ficheRando']['complements']['id']; ?>
									</td>
									<td  class="tdFicheValeur" colspan="3">
										<textarea class="normal" name="ficheComplements" style="width:99.5%" rows="5"><?php echo $_SESSION['ficheComplements']; ?></textarea>
									</td>
								</tr>
							</tbody>
						</table>
						<hr>
					</div> <!-- fiche -->
					<!--  Aide -->
					<div  id="aide"
					style="display: <?php if ($action=="imprimerTdm") echo "none"; else { if ($_SESSION['idAffiche']=="aide") echo "block"; else echo "none";} ?>;">
						<h4 title="<?php echo($GLOBALS['txt']['menuTdm']['title']); ?>"><?php echo($GLOBALS['txt']['menuTdm']['texte']); ?>
						:</h4>
                     <span style="text-align: left;">
                        <ul>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['enregistreTdm']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['enregistreTdm']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['imprimeTdm']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['imprimeTdm']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['envoyerTdmCsv']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['envoyerTdmCsv']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['recalcul']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['recalcul']['title']); ?></li>
                           
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['quitter']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['quitter']['title']); ?></li>
                        </ul>
                     </span>
                     
 						<h4 title="<?php echo($GLOBALS['txt']['menuGpx']['title']); ?>"><?php echo($GLOBALS['txt']['menuGpx']['texte']); ?>
						:</h4>
                     <span style="text-align: left;">
                        <ul>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['creeWptTrk']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['creeWptTrk']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['creeWpt']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['creeWpt']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['creeWptTrkLisse']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['creeWptTrkLisse']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['analyse']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['analyse']['title']); ?></li>								
                        </ul>
                     </span>
                    
 						<h4 title="<?php echo($GLOBALS['txt']['menuFicheRando']['title']); ?>"><?php echo($GLOBALS['txt']['menuFicheRando']['texte']); ?>
						:</h4>
                     <span style="text-align: left;">
                        <ul>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['ficheHtml']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['ficheHtml']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['fichePdf']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['fichePdf']['title']); ?></li>
                        </ul>
                     </span>
                     
  						<h4 title="<?php echo($GLOBALS['txt']['menuCarte']['title']); ?>"><?php echo($GLOBALS['txt']['menuCarte']['texte']); ?>
						:</h4>
                     <span style="text-align: left;">
                        <ul>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['carteAuto']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['carteAuto']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['cartePortrait']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['cartePortrait']['title']); ?></li>
                           <li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['cartePaysage']['texte']); ?> :
                           </span> <?php echo($GLOBALS['txt']['cartePaysage']['title']); ?></li>
                        </ul>
                     </span>

                  <br>

								
								
								
								
								
  						<h4 title="<?php echo($GLOBALS['txt']['onglets']['title']); ?>"><?php echo($GLOBALS['txt']['onglets']['texte']); ?>
						:</h4>
						<span style="text-align: left;">
							<ul>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['ongletTdm']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['ongletTdm']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['ongletProfil']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['ongletProfil']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['ongletFiche']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['ongletFiche']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['ongletAide']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['ongletAide']['title']); ?></li>
							</ul>
						</span>

						<h4><?php echo($GLOBALS['txt']['parametres']['texte']); ?>:</h4>
						<span style="text-align: left;">
							<ul>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['nomRando']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['nomRando']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['date']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['date']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['animateur']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['animateur']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['vitesse']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['vitesse']['title']); ?></li>
								
								<li>
								<span style="font-weight: bold;">méthode de calcul du temps de marche : choix entre la méthode du "mètre-effort" et la méthode du "profil de vitesse selon la pente". NB : le paramétrage de l'application peut imposer la méthode du "mètre-effort".
								</span>
								</li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['methodeEffort']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['methodeEffort']['title']); ?></li>
								<ul>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['coefPos']['texte']); ?> :
									</span> <?php echo($GLOBALS['txt']['coefPos']['title']); ?></li>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['coefNeg']['texte']); ?> :
									</span> <?php echo($GLOBALS['txt']['coefNeg']['title']); ?></li>
								</ul>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['methodeProfil']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['methodeProfil']['title']); ?></li>
								<ul>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['kPos']['texte']); ?> :
									</span> <?php echo($GLOBALS['txt']['kPos']['title']); ?></li>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['kNeg']['texte']); ?> :
									</span> <?php echo($GLOBALS['txt']['kNeg']['title']); ?></li>
								</ul>


								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['calcDeniv']['texte']); ?> 
								</span> <?php echo($GLOBALS['txt']['calcDeniv']['title']); ?></li>
								<ul>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['pointsPassage']['texte']); ?> : 
									</span> <?php echo($GLOBALS['txt']['pointsPassage']['title']); ?></li>
									<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['pointsTrace']['texte']); ?> :
									</span> <?php echo($GLOBALS['txt']['pointsTrace']['title']); ?></li>
									<ul>
										<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['distanceLissage']['texte']); ?> :
										</span> <?php echo($GLOBALS['txt']['distanceLissage']['title']); ?></li>
										<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['seuil']['texte']); ?> :
										</span> <?php echo($GLOBALS['txt']['seuil']['title']); ?></li>
									</ul>
								</ul>

								</ul>


							
							<span style="font-weight: bold; margin-left: 25px"><?php echo($GLOBALS['txt']['parametresNB']['texte']); ?> :
							</span> <?php echo($GLOBALS['txt']['parametresNB']['title']); ?>

						</span>

						<h4><?php echo($GLOBALS['txt']['colonnes']['texte']); ?>:</h4>
						<span style="text-align: left;">
							<ul>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['de']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['de']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['a']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['a']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['name']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['name']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['utm']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['utm']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['azimut']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['azimut']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['ele']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['ele']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['distance']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['distance']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['distanceCumul']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['distanceCumul']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['deniv']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['deniv']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['pente']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['pente']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['denivAdd']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['denivAdd']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['dureeAdd']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['dureeAdd']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['duree']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['duree']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['pause']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['pause']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['heureArrivee']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['heureArrivee']['title']); ?></li>
							<li><span style="font-weight: bold;"><?php echo(str_replace("<br>"," ",$GLOBALS['txt']['observ']['texte'])); ?> :
							</span> <?php echo($GLOBALS['txt']['observ']['title']); ?></li>
							</ul>
							<span style="font-weight: bold; margin-left: 25px"><?php echo($GLOBALS['txt']['colonnesNB']['texte']); ?> :
							</span> <?php echo($GLOBALS['txt']['colonnesNB']['title']); ?>

						</span>

						<h4><?php echo($GLOBALS['txt']['synthese']['texte']); ?>:
						</h4>
						<span style="text-align: left;">
							<ul>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['distanceTotale']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['distanceTotale']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['dureeTotale']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['dureeTotale']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['denivTotPos']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['denivTotPos']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['denivTotNeg']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['denivTotNeg']['title']); ?></li>
							</ul>
						</span>

						<h4><?php echo($GLOBALS['txt']['profil']['texte']); ?>:</h4>
						<span style="text-align: left;">
							<ul>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['segments']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['segments']['title']); ?></li>
								<li><span style="font-weight: bold;"><?php echo($GLOBALS['txt']['points']['texte']); ?> :
								</span> <?php echo($GLOBALS['txt']['points']['title']); ?></li>
							</ul>
						</span>

						<h4><?php echo($GLOBALS['txt']['ficheRando']['texte']); ?>:</h4>
						<span style="text-align: left;">
							<ul>
							<?php
								foreach ($GLOBALS['txt']['ficheRando']['consignes'] as $consigne) {
							?>
								<li> <?php echo($consigne); ?></li>
							<?php
								}
							?>
							</ul>
						</span>

					</div> <!-- aide -->
					<?php
					}
					?>
				</form>
				<?php
				if ($action!="imprimerTdm")
				{
				?>
				<script type="text/javascript">
					// coefficients du profil de vitesse par défaut
					coef = [<?php foreach ($GLOBALS['profilVitesse'] as $vitesse) {echo($vitesse.", ");} ?>];
				</script>
				<?php
				}
				?>

		</body>
	</html>
	<?php
	}
	// fin afficherTdm()
	////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////
	// mettre à jour Session tdm après édition
	function mettreAJourSession() {
		// contrer le magic_quote du serveur
		foreach ($_POST as $key => $value) {
			if (is_string($value)) {
				$_POST[$key] = stripslashes($value);
			}
			else {
				foreach ($value as $key1 => $value1) {
					$_POST[$key][$key1] = stripslashes($value1);
				}
			}
		}
		// zone à afficher au retour

		$_SESSION['idAffiche'] = $_POST['idAffiche'];
		$_SESSION['profilVitesse'] = $_POST['profilVitesse'];

		$_SESSION['nomRando'] = ($_POST['nomRando']);
		$_SESSION['date'] =  nationaliserDate($_POST['inputDate']);
		$_SESSION['animateur'] = ($_POST['animateur']);
		$_SESSION['vitesse'] = str_replace(",",".",$_POST['vitesse']);
		// empêcher la division par 0
		if ($_SESSION['vitesse']==0) $_SESSION['vitesse'] = 0.1;
		$_SESSION['methode'] = $_POST['methode'];
		$_SESSION['coefPos'] = $_POST['coefPos'];
		$_SESSION['coefNeg'] = $_POST['coefNeg'];
		if (isset($_POST['kPos'])) $_SESSION['kPos'] = $_POST['kPos'];
		if (isset($_POST['kNeg'])) $_SESSION['kNeg'] = $_POST['kNeg'];
		$_SESSION['calcDeniv'] = $_POST['calcDeniv'];

		if ($_SESSION['date']!="//") {
			$date = $_SESSION['date'];
			$tab_date = explode("/",$date);
			$jour = (int) $tab_date[0];
			$mois = (int) $tab_date[1];
			$an = (int) $tab_date[2];
			setlocale(LC_TIME, 'fr_FR.UTF8');
			$_SESSION['ficheDate'] = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
		}
		else {
			$_SESSION['ficheDate'] = "";
		}
		
		if (isset($_POST['distanceLissage'])) $_SESSION['distanceLissage'] = $_POST['distanceLissage'];
		if (isset($_POST['seuil'])) $_SESSION['seuil'] = $_POST['seuil'];


		// logo
		if (isset($_POST['ficheLogoChoix'])) {
			switch ($_POST['ficheLogoChoix']) {
				case "noChange":

					break;
				case "other":
					$fichier = $_FILES['ficheLogoUrl']['tmp_name'];
					$fp = fopen($fichier, "rb");
					clearstatcache();
					$data = fread($fp,filesize($fichier));
					fclose($fp);
					$_SESSION['ficheLogoImage'] = chunk_split(base64_encode($data));
					// type de l'image
					$_SESSION['ficheLogoImageType'] = substr(LOGO_URL,strrpos(LOGO_URL,".")+1);
					$_SESSION['ficheLogoAucun'] = FALSE;
					break;
				case "default":
					$fp = fopen(LOGO_URL,'rb');
					$data = fread($fp,filesize(LOGO_URL));
					fclose($fp);
					$_SESSION['ficheLogoImage'] = chunk_split(base64_encode($data));
					// type de l'image
					$_SESSION['ficheLogoImageType'] = substr(LOGO_URL,strrpos(LOGO_URL,".")+1);
					$_SESSION['ficheLogoAucun'] = FALSE;
					break;
				case "none":
					$_SESSION['ficheLogoAucun'] = TRUE;;
					break;
			}
		}
		
		$_SESSION['ficheTitreFiche'] = ($_POST['ficheTitreFiche']);
		$_SESSION['ficheSousTitre'] = ($_POST['ficheSousTitre']);
		$_SESSION['fichePresentation'] = $_POST['fichePresentation'];
		$_SESSION['ficheRemarques'] = $_POST['ficheRemarques'];

		if (THEME) {
			$_SESSION['ficheThemeCode'] = $_POST['ficheThemeCode'];
			$_SESSION['ficheThemeDescription'] = ($_POST['ficheThemeDescription']);
		}

		$_SESSION['ficheNiveau'] = ($_POST['ficheNiveau']);
		
		if (IBP) $_SESSION['ficheIbpIndex'] = $_POST['ficheIbpIndex'];
		
		
		$_SESSION['ficheDenivPosFiche'] = $_POST['ficheDenivPosFiche'];
		$_SESSION['ficheDenivNegFiche'] = $_POST['ficheDenivNegFiche'];
		$_SESSION['ficheLongueurFiche'] = $_POST['ficheLongueurFiche'];
		$_SESSION['ficheDureeFiche'] = $_POST['ficheDureeFicheH']*60+$_POST['ficheDureeFicheM'];

		$_SESSION['ficheDifficultes'] = ($_POST['ficheDifficultes']);
		$_SESSION['ficheCarte'] = ($_POST['ficheCarte']);
		$_SESSION['ficheRDV'] = $_POST['ficheRDV'];
		$_SESSION['ficheDepart'] = ($_POST['ficheDepart']);
		$_SESSION['ficheTrajet'] = $_POST['ficheTrajet'];
		
		$_SESSION['ficheItiXmlSans'] = $_POST['ficheItiXmlSans'];
		$_SESSION['ficheItiXmlAvec'] = $_POST['ficheItiXmlAvec'];
		$_SESSION['ficheItiModeVisualiserSans'] = ($_POST['ficheItiModeVisualiserSans']);
		$_SESSION['ficheItiModeVisualiserAvec'] = ($_POST['ficheItiModeVisualiserAvec']);
		$_SESSION['ficheItiTitrePageSans'] = ($_POST['ficheItiTitrePageSans']);
		$_SESSION['ficheItiTitrePageAvec'] = ($_POST['ficheItiTitrePageAvec']);
		
		if (OPENSERVICE_ROUTE) {
			$_SESSION['ficheItiDepartLon'] = $_POST['ficheItiDepartLon'];
			$_SESSION['ficheItiDepartLat'] = $_POST['ficheItiDepartLat'];
			$_SESSION['ficheItiArriveeLon'] = $_POST['ficheItiArriveeLon'];
			$_SESSION['ficheItiArriveeLat'] = $_POST['ficheItiArriveeLat'];
		}
		
		$_SESSION['ficheParking'] = ($_POST['ficheParking']);
		$_SESSION['ficheTrajetKm'] = ($_POST['ficheTrajetKm']);
		$_SESSION['ficheCovoiturage'] = ($_POST['ficheCovoiturage']);
		$_SESSION['ficheLaRandonnee'] = ($_POST['ficheLaRandonnee']);
		$_SESSION['ficheEquipement'] = ($_POST['ficheEquipement']);
		
		$_SESSION['ficheAnimateur'] = $_SESSION['animateur'];
	/*	
		if ($_POST['ficheAnimateur']!="") $_SESSION['ficheAnimateur'] = ($_POST['ficheAnimateur']);
		else $_SESSION['ficheAnimateur'] = $_SESSION['animateur'];
	*/	
		$_SESSION['ficheComplementsTitre'] = ($_POST['ficheComplementsTitre']);
		$_SESSION['ficheComplements'] = ($_POST['ficheComplements']);

		// name
		foreach ($_POST['name'] as $i => $name) { $_SESSION['pdp'][$i]['name']= ($name);}
		// ele
		foreach ($_POST['ele'] as $i => $ele) { $_SESSION['pdp'][$i]['ele']= str_replace(",",".",$ele);}
		// denivAdd
		if (isset($_POST['denivAdd'])) {
			foreach ($_POST['denivAdd'] as $i => $denivAdd) { $_SESSION['pdp'][$i]['denivAdd'] = str_replace(",",".",$denivAdd);}
		}
		// dureeAdd
		foreach ($_POST['dureeAdd'] as $i => $dureeAdd) { $_SESSION['pdp'][$i]['dureeAdd'] = str_replace(",",".",$dureeAdd);}
		// pause
		foreach ($_POST['pause'] as $i => $pause) { $_SESSION['pdp'][$i]['pause'] = str_replace(",",".",$pause);}
		// observ
		foreach ($_POST['observ'] as $i => $observ) { $_SESSION['pdp'][$i]['observ'] = ($observ);}

		// heure d'arrivée sur les lieux de la rando
		$ha = explode(":",$_POST['heureArrivee']);
		$_SESSION['pdp'][0]['heure'] = $ha[0]*60+$ha[1];

	}
	// fin mettreAJourSession()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// construireAnalyse
	function construireAnalyse() {
		// analyse de la trace : dénivelée et pente entre les points de trace
		$analyse = $_SESSION['trk'];
		$dist = 0;
		$denivPos = 0;
		$denivNeg = 0;
		
		foreach ($analyse as $i => $unPt) {
			if ($i==0) {
				$analyse[$i]['distance'] = 0;
				$analyse[$i]['deniv'] = 0;
				$analyse[$i]['pentePC'] =0;
				$analyse[$i]['penteDeg'] = 0;
				$analyse[$i]['distanceCumul'] = 0;
				$analyse[$i]['denivPos'] = 0;
				$analyse[$i]['denivNeg'] = 0;
			}
			else {
				$analyse[$i]['distance'] = distance($analyse[$i-1]['lat'],$analyse[$i-1]['lon'],$analyse[$i]['lat'],$analyse[$i]['lon']);
				$dist += $analyse[$i]['distance'];
				$analyse[$i]['distanceCumul'] = $dist;
				$deniv = $analyse[$i]['ele']-$analyse[$i-1]['ele'];
				if ($deniv>0) $denivPos += $deniv;
				else $denivNeg += $deniv;
				$analyse[$i]['deniv'] = $deniv;
				$analyse[$i]['denivPos'] = $denivPos;
				$analyse[$i]['denivNeg'] = $denivNeg;
				
				if ($analyse[$i]['distance']!=0) {
					$analyse[$i]['pentePC'] = $analyse[$i]['deniv']/$analyse[$i]['distance'];
					$analyse[$i]['penteDeg'] = atan($analyse[$i]['pentePC'])*180/pi();
				}
				else {
					$analyse[$i]['pentePC'] = "";
					$analyse[$i]['penteDeg'] = "";
				}
			}
		}
		
		$xml = '
	<!DOCTYPE html>
	<html lang="fr-fr">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="content-type">
		<title>analyse</title>
	</head>
	<body style="font-family:sans-serif;">
		<p>Analyse de la trace : '.$_SESSION['nomRando'].'</p>
		<table style="width: 100%; text-align: center;" border="1">
			<tbody>
			<tr>
				<td style="text-align: center;" colspan="5" rowspan="1">points de
					trace</td>
				<td style="text-align: center;" colspan="4" rowspan="1">par rapport au
					précédent</td>
				<td style="text-align: center;" colspan="2" rowspan="1">cumulé</td>
			</tr>
			<tr>
				<td>n°</td>
				<td>distance cumulée</td>
				<td>longitude</td>
				<td>latitude</td>
				<td>altitude</td>
				<td>distance</td>
				<td>dénivelée</td>
				<td>pente en %</td>
				<td>pente en °</td>
				<td>déniv. +</td>
				<td>déniv. -</td>
			</tr>
			';
		foreach ($analyse as $i => $unPt) {
			$xml .="\t\t\t\t<tr>\n";
			$xml .= "\t\t\t\t\t<td>".$i."</td>\n";
			$xml .= "\t\t\t\t\t<td>".round($analyse[$i]['distanceCumul'])." m</td>\n";
			$xml .= "\t\t\t\t\t<td>".(round($analyse[$i]['lon']*1000000)/1000000)." °</td>\n";
			$xml .= "\t\t\t\t\t<td>".(round($analyse[$i]['lat']*1000000)/1000000)." °</td>\n";
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['ele']." m</td>\n";
			$xml .= "\t\t\t\t\t<td>".(round($analyse[$i]['distance']*100)/100)." m</td>\n";
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['deniv']." m</td>\n";

			if ($analyse[$i]['distance']!=0) {
				$xml .= "\t\t\t\t\t<td>".round($analyse[$i]['pentePC']*100)." %</td>\n";
				$xml .= "\t\t\t\t\t<td>".round($analyse[$i]['penteDeg'])." °</td>\n";
			}
			else {
				$xml .= "\t\t\t\t\t<td>-</td>\n";
				$xml .= "\t\t\t\t\t<td>-</td>\n";
			}
	/*
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['pentePC']." m</td>\n";
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['penteDeg']." m</td>\n";
	*/
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['denivPos']." m</td>\n";
			$xml .= "\t\t\t\t\t<td>".$analyse[$i]['denivNeg']." m</td>\n";
			$xml .="\t\t\t\t</tr>\n";
		}
		$xml .= '
			</tbody>
		</table>
	</body>
	</html>
		
		';
		
		return $xml;
	}
	// fin construireAnalyse()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// envoyerAnalyse
	function envoyerAnalyse($xml) {
		$contentType = "text/xml";
		$longueur = strlen($xml);
		$nom = "Analyse_".$_SESSION['nomRando'].".html";
		
			header("Content-Type: text/html");
			header("Content-Length: ".$longueur."\"");
	//		header('Content-Disposition: attachment; filename="'.$nom."\"'");
			header('Content-Disposition: attachment; filename="'.$nom.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($xml);
		exit();
	}
	// fin envoyerAnalyse()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// construireTdm
	function construireTdm() {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<tdm>\n";
		foreach ($_SESSION as $key => $value) {
			if ($key!="connexion") { // pour ne pas intégrer au TDM les éléments de connexion
				if ($key=='ficheItiXmlSans' || $key=='ficheItiXmlAvec') {
					// retirer l'en-tête XML et gpx et retirer la balise </gpx>
					$posGpx = strpos($value,'<trk');
					$value = substr($value,$posGpx);
					$posGpx = strpos($value,'</gpx>');
					$value = substr($value,0,$posGpx);
					$xml .= "\t<". $key.">".$value."</". $key.">"."\n";
				}
				else {
					if ($key!="gpxIgnIni") {
						if (!is_array($value)) {
							$xml .= "\t<". $key.">".htmlspecialchars($value, ENT_NOQUOTES)."</". $key.">"."\n";
						}
						else {
							$xml .= "\t<". $key.">"."\n";
							foreach ($value as $key1 => $value1) {
								$xml .= "\t\t<". $key."pt>"."\n";
								foreach ($value1 as $key2 => $value2) {
									if (!is_array($value2)){
										$xml .= "\t\t\t<". $key2.">".htmlspecialchars($value2, ENT_NOQUOTES)."</". $key2.">"."\n";
									}
									else {
										$xml .= "\t\t\t<". $key2.">\n";
										foreach ($value2 as $key3 => $value3) {
											$xml .= "\t\t\t\t<". $key3.">".htmlspecialchars($value3, ENT_NOQUOTES)."</". $key3.">"."\n";
										}
										$xml .= "\t\t\t</". $key2.">"."\n";
									}
								}
								$xml .= "\t\t</". $key."pt>"."\n";
							};
							$xml .= "\t</". $key.">"."\n";

						}
					}
				}
			} // fin si pas connexion
		}
		$xml .= "</tdm>";

		return $xml;
	}
	// fin construireTdm()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// envoyerTdm
	function envoyerTdm($xml) {
		$contentType = "text/xml";
		$longueur = strlen($xml);
		$nom = $_SESSION['nomRando'].".tdm";
			header("Content-Type: text/xml");
			header("Content-Length: ".$longueur."\"");
	//		header('Content-Disposition: attachment; filename="'.$nom."\"'");
			header('Content-Disposition: attachment; filename="'.$nom.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($xml);
		exit();
	}
	// fin envoyerTdm()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// construire TDM en CSV
	function construireTdmCSV(){
		

		// calcul des cumuls pour la synthèse
		$n = count($_SESSION['pdp'])-1;

		$distanceCumul =  $_SESSION['pdp'][$n]['distanceCumul']; //round($_SESSION['pdp'][$n]['distanceCumul']/1000,1);
		if ($_SESSION['calcDeniv']=="passage") {
			$denivCumulPos = $_SESSION['pdp'][$n]['denivCumulPos'];
			$denivCumulNeg = $_SESSION['pdp'][$n]['denivCumulNeg'];
		}
		else {
			$denivCumulPos = 0;
			$denivCumulNeg = 0;
			foreach ($_SESSION['pdp'] as $i => $unPdp) {
				$denivCumulPos += $unPdp['denivPos'];
				$denivCumulNeg += $unPdp['denivNeg'];
			}
		}
		$dureeCumul = 0;
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$dureeCumul += $_SESSION['pdp'][$i]['duree'];
		}
		$dureeCumul = round($dureeCumul,0);
/*
		// initialisation des valeurs-cumuls de la fiche rando

		if ($_SESSION['ficheDenivPosFiche']=="") $_SESSION['ficheDenivPosFiche'] = $denivCumulPos;
		if ($_SESSION['ficheDenivNegFiche']=="") $_SESSION['ficheDenivNegFiche'] = abs($denivCumulNeg);
		if ($_SESSION['ficheLongueurFiche']=="") $_SESSION['ficheLongueurFiche'] = $distanceCumul;
		if ($_SESSION['ficheDureeFiche']=="") $_SESSION['ficheDureeFiche'] = $dureeCumul;
*/
	
		// remplacement de <br> par espace dans les textes
		foreach ($GLOBALS['txt'] AS $item => $unTxt) {
			$txt[$item]['texte'] = str_replace('<br>',' ',$unTxt['texte']);
		}

		// construction du contenu du fichier csv
		$csv = "";

		// TOP
		
		// BODY titres
		if ($_SESSION['calcDeniv']=="passage") {
		$selonCalculDeniv = "{$txt['deniv']['texte']}\t{$txt['pente']['texte']}\t{$txt['denivAdd']['texte']}\t";
		}
		else {
		$selonCalculDeniv = "{$txt['denivPos']['texte']}\t{$txt['denivNeg']['texte']}\t{$txt['pentePos']['texte']}\t{$txt['penteNeg']['texte']}\t";
		}
		
		$csv = "{$txt['de']['texte']}\t{$txt['a']['texte']}\t{$txt['name']['texte']}\t{$txt['utm']['texte']}\t{$txt['azimut']['texte']}\t{$txt['ele']['texte']}\t{$txt['distance']['texte']}\t{$txt['distanceCumul']['texte']}\t$selonCalculDeniv{$txt['dureeAdd']['texte']}\t{$txt['duree']['texte']}\t{$txt['pause']['texte']}\t{$txt['heureArrivee']['texte']}\t{$txt['observ']['texte']}\n";
		
		// BODY lignes
		foreach($_SESSION['pdp'] as $i =>$pdp) {
			$de = chr(65+($i-1)%26);
			if ($i>26) $de = $de . floor($i/26);
			$a = chr(65+$i%26);
			if ($i+1>26) $a = $a . floor(($i+1)/26);

			if ($i>0) $csv .= "{$GLOBALS['txt']['de']['texte']} $de\t";
			else $csv .= " \t";
			if ($i>0) $csv .= "{$GLOBALS['txt']['a']['texte']} $a\t";
			else $csv .= "$a\t";
			$name = escapeCSV($pdp['name']);
			$csv .= "$name\t";
			$format1= '%1$d%2$s %3$08.3F';
			$format2= 'UTM %1$08.3F';
	//		$format1= '%1$d%2$ %3$08.3F';
	//		$format2= 'UTM %1$08.3F';
			$utm = sprintf($format1, $pdp['utm']['zone'], $pdp['utm']['lettre'], $pdp['utm']['Est']).' ' .sprintf($format2, $pdp['utm']['Nord']);
			$csv .= "$utm\t";
			
			if ($i>0)  $azimut = round($pdp['azimut']);	
			else $azimut = "";
			$csv .= "$azimut\t";
			$csv .= "{$pdp['ele']}\t";
			if ($i>0)  $distance = round($pdp['distance']);	
			else $distance = "";
			$csv .= "$distance\t";
			if ($i>0)  $distanceCumul = round($pdp['distanceCumul']);	
			else $distanceCumul = "";
			$csv .= "$distanceCumul\t";
			if ($_SESSION['calcDeniv']=="passage") {
				if ($i>0)  $deniv = round($pdp['deniv']);	
				else $deniv = "";
				$csv .= "$deniv\t";
				if ($i>0)  $pente = round($pdp['pente']);	
				else $pente = "";
				$csv .= "$pente\t";
				if ($i>0)  $denivAdd = round($pdp['denivAdd']);	
				else $denivAdd = "";
				$csv .= "$denivAdd\t";
			}
			else {
				if ($i>0)  $denivPos = round($pdp['denivPos']);	
				else $denivPos = "";
				$csv .= "$denivPos\t";
				if ($i>0)  $denivNeg = round($pdp['denivNeg']);	
				else $denivNeg = "";
				$csv .= "$denivNeg\t";
				if ($i>0)  $pentePos = round($pdp['pentePos']);	
				else $pentePos = "";
				$csv .= "$pentePos\t";
				if ($i>0)  $penteNeg = round($pdp['penteNeg']);	
				else $penteNeg = "";
				$csv .= "$penteNeg\t";
			}
			if ($i>0)  $dureeAdd = round($pdp['dureeAdd']);	
			else $dureeAdd = "";
			$csv .= "$dureeAdd\t";
			if ($i>0)  $duree = round($pdp['duree']);	
			else $duree = "";
			$csv .= "$duree\t";
			if ($i>0)  $pause = round($pdp['pause']);	
			else $pause = "";
			$csv .= "$pause\t";
			$h = floor($pdp['heure']/60);
			$m = $pdp['heure']%60;
			if ($h<10) $h = 0 . $h;
			if ($m<10) $m = 0 . $m;
			$csv .= "$h:$m\t";
			$observ = escapeCSV($pdp['observ']);
			$csv .= "$observ\n";



		}
		if ($_SESSION['calcDeniv']=="passage") {
		// BOTTOM cumuls sur points de trace
			$csv .= "cumul\t\tdu départ à l'arrivée\t\t\t\t$distanceCumul\t\t+$denivCumulPos\t\t\t\t$dureeCumul\t\t\t\n";
			$csv .= "\t\t\t\t\t\t\t\t$denivCumulNeg\t\t\t\t\t\t\n";
	}
		
		else {
		// BOTTOM cumuls sur points de passage
			$csv .= "cumul\t\tdu départ à l'arrivée\t\t\t\t$distanceCumul\t\t$denivCumulPos\t$denivCumulNeg\t\t\t\t$dureeCumul\t\t\t\n";
		}
		

		$nbPdp = count($_SESSION['pdp']);
		$nbTrk = count($_SESSION['trk']);
		// GENERAL fin de fichier
		$csv .= "\n";
		$csv .= "\t\t\t{$_SESSION['nomRando']}\n";
		$csv .= "\t\t\tnb points de passage\t$nbPdp\n";
		$csv .= "\t\t\tnb points de trace\t$nbTrk\n";
		$csv .= "\t\t\tvitesse à plat\t{$_SESSION['vitesse']}\n";
		// selon effort ou pente
		if ($_SESSION['methode']=="effort") {
			$csv .= "\t\t\tdéniv => mètre-effort\n";
			$csv .= "\t\t\t - coeff. déniv. +\t{$_SESSION['coefPos']}\n";
			$csv .= "\t\t\t - coeff. déniv. -\t{$_SESSION['coefNeg']}\n";
		}
		else {
			$csv .= "\t\t\tpofil de vitesse selon pente\n";
			$csv .= "\t\t\t - effet en montée\t{$_SESSION['kPos']}\n";
			$csv .= "\t\t\t - effet en descente\t{$_SESSION['kNeg']}\n";
		}
		// points  de passage ou de trace
		if ($_SESSION['calcDeniv']=="passage") {
			$csv .= "\t\t\tdéniv. calculées sur points de\tpassage\n";
		}
		else {
			$csv .= "\t\t\tdéniv. calculées sur points de\ttrace\n";
			$csv .= "\t\t\t - moyenne sur\t{$_SESSION['distanceLissage']}\n";
			$csv .= "\t\t\t - seuil de déniv.\t{$_SESSION['seuil']}\n";
		}
		
		return($csv);

	}
	// fin construireTdmCSV()
	////////////////////////////////////////////////////////////////////////////////

	
	////////////////////////////////////////////////////////////////////////////////
	// envoyer TDM en CSV
	function envoyerTdmCSV($csv){
	
	// Content-Type: text/html; charset=utf-8
		$contentType = "text/csv";
		$longueur = strlen($csv);

		$nom = "{$_SESSION['nomRando']}_tdm.csv";
		header('Content-Encoding: UTF-8');
		header("Content-Type: text/csv; charset=UTF-8");
		header("Content-Length: ".$longueur."\"");
		header('Content-Disposition: attachment; filename="'.$nom.'"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($csv);
		exit();

	}
	// fin envoyerTdmCSV()
	////////////////////////////////////////////////////////////////////////////////




	////////////////////////////////////////////////////////////////////////////////
	// construireTrk
	function construireTrk() {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$xml .= '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="gpx2tdm">'."\n";
		// les waypoints points de passage
		foreach ($_SESSION['pdp'] as $i => $wpt) {
			$xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
			$xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_QUOTES)."</name>\n";
			$xml .= "\t</wpt>\n";
		}
		// les waypoints hors de la trace
		if (isset($_SESSION['w'])) {
         foreach ($_SESSION['w'] as $i => $wpt) {
            $xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
            $xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_QUOTES)."</name>\n";
            $xml .= "\t</wpt>\n";
         }
      }
		// la trace
		$xml .= "\t<trk>\n";
		$xml .= "\t\t<name>".htmlspecialchars($_SESSION['nomRando'], ENT_QUOTES)."</name>\n";
		$xml .= "\t\t<trkseg>\n";
		foreach ($_SESSION['trk'] as $i => $trkpt) {
			$xml .= "\t\t\t<trkpt lat=\"".$trkpt['lat']."\" lon=\"".$trkpt['lon']."\">\n";
			$xml .= "\t\t\t\t<ele>".$trkpt['ele']."</ele>\n";
			$xml .= "\t\t\t</trkpt>\n";
		}
		$xml .= "\t\t</trkseg>\n";
		$xml .= "\t</trk>\n";

		$xml .= "</gpx>\n";
	//die($xml);
		return $xml;
	}
	// fin construireTrk()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// envoyerTrk
	function envoyerTrk($xml) {
		$contentType = "text/xml";
		$longueur = strlen($xml);

		// suppression de _wpt_trk.gpx (tout ou partie)
		$p = strpos($_SESSION['nomRando'],'_wpt');
		if ($p===FALSE) {
			$nomCourt = $_SESSION['nomRando'];
		}
		else {
			$nomCourt = substr($_SESSION['nomRando'],0,$p);
		}
		$p = strpos($nomCourt,'_trk');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}

		$p = strpos($nomCourt,'.gpx');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}
		if ($nomCourt=="") $nomCourt = "rando";
		// fin du nom initialisée
		$finNom = "_wpt_trk.gpx";
		$nom = $nomCourt.$finNom;

		
			header("Content-Type: text/xml");
			header("Content-Length: ".$longueur."\"");
	//		header('Content-Disposition: attachment; filename="'.$nom."\"'");
			header('Content-Disposition: attachment; filename="'.$nom.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($xml);
		exit();
	}
	// fin envoyerTrk()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// construireTrkLisse
	function construireTrkLisse() {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$xml .= '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="gpx2tdm">'."\n";
		// les waypoints points de passage
		foreach ($_SESSION['pdp'] as $i => $wpt) {
			$xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
			$xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_QUOTES)."</name>\n";
			$xml .= "\t</wpt>\n";
		}
		// les waypoints hors de la trace
		if (isset($_SESSION['w'])) {
         foreach ($_SESSION['w'] as $i => $wpt) {
            $xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
            $xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_QUOTES)."</name>\n";
            $xml .= "\t</wpt>\n";
         }
      }
		// la trace lissée
		$xml .= "\t<trk>\n";
		$xml .= "\t\t<name>".htmlspecialchars($_SESSION['nomRando'], ENT_QUOTES)."</name>\n";
		$xml .= "\t\t<trkseg>\n";
		foreach ($_SESSION['trkLisse'] as $i => $trkpt) {
			$xml .= "\t\t\t<trkpt lat=\"".$trkpt['lat']."\" lon=\"".$trkpt['lon']."\">\n";
			$xml .= "\t\t\t\t<ele>".$trkpt['ele']."</ele>\n";
			$xml .= "\t\t\t</trkpt>\n";
		}
		$xml .= "\t\t</trkseg>\n";
		$xml .= "\t</trk>\n";

		$xml .= "</gpx>\n";
	//die($xml);
		return $xml;
	}
	// fin construireTrkLisse()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// envoyerTrkLisse
	function envoyerTrkLisse($xml) {
		$contentType = "text/xml";
		$longueur = strlen($xml);

		// suppression de _wpt_trk.gpx (tout ou partie)
		$p = strpos($_SESSION['nomRando'],'_wpt');
		if ($p===FALSE) {
			$nomCourt = $_SESSION['nomRando'];
		}
		else {
			$nomCourt = substr($_SESSION['nomRando'],0,$p);
		}
		$p = strpos($nomCourt,'_trk');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}

		$p = strpos($nomCourt,'.gpx');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}
		if ($nomCourt=="") $nomCourt = "rando";
		// fin du nom initialisée
		$finNom = "_LISSE_wpt_trk.gpx";
		$nom = $nomCourt.$finNom;

		
			header("Content-Type: text/xml");
			header("Content-Length: ".$longueur."\"");
	//		header('Content-Disposition: attachment; filename="'.$nom."\"'");
			header('Content-Disposition: attachment; filename="'.$nom.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($xml);
		exit();
	}
	// fin envoyerTrkLisse()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// construireWpt
	function construireWpt() {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$xml .= '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="gpx2tdm">'."\n";
		// les waypoints
		foreach ($_SESSION['pdp'] as $i => $wpt) {
			$xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
			$xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_NOQUOTES)."</name>\n";
			$xml .= "\t\t<desc>".htmlspecialchars($wpt['observ'], ENT_NOQUOTES)."</desc>\n";
			$xml .= "\t</wpt>\n";
		}
		// les waypoints hors de la trace
		if (isset($_SESSION['w'])) {
         foreach ($_SESSION['w'] as $i => $wpt) {
            $xml .= "\t<wpt lat=\"".$wpt['lat']."\" lon=\"".$wpt['lon']."\">\n";
            $xml .= "\t\t<name>".htmlspecialchars($wpt['name'], ENT_NOQUOTES)."</name>\n";
            $xml .= "\t</wpt>\n";
         }
      }

		$xml .= "</gpx>\n";
		return $xml;
	}
	// fin construireWpt()
	////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// envoyerWpt
	function envoyerWpt($xml) {
		$contentType = "text/xml";
		$longueur = strlen($xml);

		// suppression de _wpt_trk.gpx (tout ou partie)
		$p = strpos($_SESSION['nomRando'],'_wpt');
		if ($p===FALSE) {
			$nomCourt = $_SESSION['nomRando'];
		}
		else {
			$nomCourt = substr($_SESSION['nomRando'],0,$p);
		}
		$p = strpos($nomCourt,'_trk');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}

		$p = strpos($nomCourt,'.gpx');
		if ($p===FALSE) {
			$nomCourt = $nomCourt;
		}
		else {
			$nomCourt = substr($nomCourt,0,$p);
		}
		if ($nomCourt=="") $nomCourt = "rando";
		// fin du nom initialisée
		$finNom = "_wpt.gpx";
		$nom = $nomCourt.$finNom;

			header("Content-Type: text/xml");
			header("Content-Length: ".$longueur."\"");
	//		header('Content-Disposition: attachment; filename="'.$nom."\"'");
			header('Content-Disposition: attachment; filename="'.$nom.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		//	header('Cache-Control: private');
		//	header('Pragma:  no-cache');
		echo ($xml);
		exit();
	}
	// fin envoyerWpt()
	////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
// fonctions diverses
////////////////////////////////////////////////////////////////////////////////

	// fonction indexMinimum  qui donne l'index dans trk du point tel que la distance à wpt cesse de diminuer
	function indexMinimum($wpt, $kTrouve, $kMax) {
		$distMin = 1000000;
		$k = $kTrouve+1;
		$fin = $kTrouve==$kMax;
		$dPrec = 0;
		while (!$fin) {
			$d = distance($_SESSION['trk'][$k]['lat'],$_SESSION['trk'][$k]['lon'], $wpt['lat'], $wpt['lon']);
			if ($d>$distMin and $dPrec<100) {
				return ($k-1);
				$fin = TRUE;
			}
			else {
				$distMin = $d;
				$dPrec = $d;
				$k++;
				$fin = $k>$kMax;
			}
		}
		return -1;
	}


	
	// fonction indexPlusProche qui donne l'index dans trk du point le plus proche du wpt
	function indexPlusProche($wpt) {
		$distMin = 1000000000;
		foreach ($_SESSION['trk'] as $i => $trkpt) {
			$d = distance($trkpt['lat'], $trkpt['lon'],$wpt['lat'],$wpt['lon']);
			if ($d<$distMin) {
				$j = $i;
				$distMin = $d;
			}
		}
		return $j;
	}

	// fonction distanceIndexPlusProche qui donne l'index et la distance dans trk du point le plus proche du wpt
	function distanceIndexPlusProche($wpt) {
		$distMin = 1000000000;
		foreach ($_SESSION['trk'] as $i => $trkpt) {
			$d = distance($trkpt['lat'], $trkpt['lon'],$wpt['lat'],$wpt['lon']);
			if ($d<$distMin) {
				$j = $i;
				$distMin = $d;
			}
		}
		$dipp['distance'] = $distMin;
		$dipp['index'] = $j;
		return $dipp;
	}

	// fonction alerter si gpx non valide
	function alerterEtRetour($message) {
	?>
		<html>
		<head>
		<meta content="text/html; charset=UTF-8" http-equiv="content-type">
		<title></title>
		<script type="text/javascript">

		function alerteRedirection() {
			alert("gpx2tdm :\n\n"+"<?php echo $message ?>");
//			window.location.href = "gpx2tdm.php";
			window.location.href = "index.php";
		}


		</script>
		</head>
		<body onLoad="alerteRedirection();">
		<br>
		</body>
		</html>
	<?php
	}

	// conversion des coordonnées géographiques en coordonnées UTM (WGS84)
	function geo2utm ($lat, $lon) { // p:latitude, l:longitude en degrés décimaux
		$lambda = $lon*M_PI/180; // longitude en radians
		$phi = $lat*M_PI/180; // latitude en radians
		$tmp = ((int) (abs($lon)/6))*6+3;
		if ($lon>=0) $lambda0deg = $tmp; // longitude du méridien de référence en °
		else $lambda0deg = -$tmp;
		$lambda0 = $lambda0deg * M_PI /180; // longitude du méridien de référence en radians
		$zone =  $lambda0deg/6+30.5;
//		$lettre = chr(((int) (abs($lat)/8))+79);
		$lettres = 'CDEFGHJKLMNPQRSTUVWXX';
		$lettre = substr($lettres,floor($lat/8.0+10.0),1);
		$f = 1/298.257223563 ; // aplatissement
		$e = sqrt(2*$f-pow($f,2)); // excentricité
		$a = 6378.137; // rayon à l'équateur
		$k0 = 0.9996; // coefficient de réduction
		if ($phi>=0) $N0 = 0;
		else $N0 = 10000;

		$nuPhi = 1/sqrt(1-pow($e,2)*pow(sin($phi),2));
		$A = ($lambda-$lambda0)*cos($phi);
		$sPhi = (1-pow($e,2)/4-3*pow($e,4)/64-5*pow($e,6)/256)*$phi-(3*pow($e,2)/8+3*pow($e,4)/32+45*pow($e,6)/1024)*sin(2*$phi)+(15*pow($e,4)/256+45*pow($e,6)/1024)*sin(4*$phi)-35*pow($e,6)/3072*sin(6*$phi);
		$T = pow(tan($phi),2);
		$C = pow($e,2)/(1-pow($e,2))*pow(cos($phi),2);

		$E = round(500+$k0*$a*$nuPhi*($A+(1-$T+$C)*pow($A,3)/6+(5-18*$T+pow($T,2))*pow($A,5)/120),3);
		$N = round($N0+$k0*$a*($sPhi+$nuPhi*tan($phi)*(pow($A,2)/2+(5-$T+9*$C+4*pow($C,2))*pow($A,4)/24+(61-58*$T+pow($T,2))*pow($A,6)/720)),3);
		$res['zone'] = $zone;
		$res['lettre'] = $lettre;
		$res['Est'] = $E;
		$res['Nord'] = $N;

		return $res;
	}

	// échappement des caractères spéciaux pour XML
	function escapeXML($ch) {
		if (is_string($ch)) {
			$ch = str_replace("&amp;"," ",$ch);
			$ch = str_replace("&lt;"," ",$ch);
		}
		return($ch);
	}
	// échappement des caractères spéciaux pour HTML
	function escapeHTML($ch) {
		if (is_string($ch)) {
			$ch = str_replace("<","&lt;",$ch);
			$ch = str_replace(">","&gt;",$ch);
			$ch = str_replace("\"","&quot;",$ch);
			$ch = str_replace("'","&#039;",$ch);
		}
		return($ch);
	}

	// échappement des caractères spéciaux pour CSV
	function escapeCSV($ch) {
		if (is_string($ch)) {
			$ch = str_replace("&amp;","&",$ch);
			$ch = str_replace("&lt;","<",$ch);
			$ch = str_replace("&gt;",">",$ch);
			$ch = str_replace("&quot;","\"",$ch);
			$ch = str_replace("&#039;","'",$ch);
			$ch = str_replace("&apos;","'",$ch);
		}
		return($ch);
	}

	// offset du trkpt à supprimer pour réduire la trace
	function offsetASupprimer($trkModifiee) {
		$distMin = 1000000;
		$imax = count($trkModifiee)-1;
		$k = 0;
		$i = 0;
		reset($trkModifiee);
		foreach ($trkModifiee as $j => $val) {
			if ($i==0) {
				$prec = $j;
			}
			else {
				if ($i==1) {
					$courant = $j;
				}
				else {
					$distance = $trkModifiee[$prec]['dist']+$trkModifiee[$courant]['dist'];
					if (($distance)<$distMin){
						$distMin = $distance;
						$k = $courant;
						$trkModifiee[$prec]['dist'] += $trkModifiee[$courant]['dist'];
					}
					$prec = $courant;
					$courant = $j;
				}
			}
			$i++;
		}
		return $k;
	}
	// fin fonctions diverses
////////////////////////////////////////////////////////////////////////////////

?>

