<?php
// util/openrouteservice.php

require '../inc/config.inc.php';
require '../inc/common.inc.php';

	//define ("COORDRDV","4.993505,43.501944");

	////////////////////////////////////////////////////////////////////////////////
	// OpenRoute => km route et temps
	////////////////////////////////////////////////////////////////////////////////
	function openRouteService($coord, $tollways) { // tollways true false
		
	// PB : limite de 40 requêtes par minute : pas assez pour récupérer la commune des 2 itinéraires
		if (!$tollways) $option = "&options={\"avoid_features\":\"tollways\"}";
		else $option = "";
		if ($tollways) $cle = CLE_OPENROUTE_1;
		else $cle = CLE_OPENROUTE_2;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://api.openrouteservice.org/v2/directions/driving-car/geojson");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_POST, TRUE);
		/*
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"coordinates":[[8.681495,49.41461],[8.686507,49.41943],[8.687872,49.420318]]}');
		*/
		$postfields = '{"coordinates":['.$coord.'],"geometry_simplify":"true","instructions_format":"text","language":"fr","units":"m","geometry":"true"}';
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

		$httpheader = array(  
			"Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8",
			"Authorization: ".$cle,
			"Content-Type: application/json; charset=utf-8"
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

		$reponse = curl_exec($ch);
		curl_close($ch);

		// détection d'une erreur openrouteservice
		if (substr($reponse,0,8)=="{\"error\"") {
			return FALSE;
		}

		
		$json = json_decode($reponse, true); // true=> assoc default false=> object
		$km = $json['features'][0]['properties']['summary']['distance']/1000;
		$openRoute['km'] = round($km); 

		// temps
		$openRoute['temps'] = (float)$json['features'][0]['properties']['summary']['duration'];
		$duree = $openRoute['temps'];
		$h = floor($duree/3600);
		$m = round(($duree%3600)/60);
		if ($h>1) $tempsAffiche = $h.' heures '.$m;
		else  $tempsAffiche = $h.' heure '.$m;
		$openRoute['tempsAffiche'] = $tempsAffiche;
		
		// gpx trk
		$openRoute['gpxTrk'] = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$openRoute['gpxTrk'] .= '<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd" version="1.1" creator="gpxRando">'."\n";
		$openRoute['gpxTrk'] .= "\t<trk>\n";
		$openRoute['gpxTrk'] .= "\t\t<name>Trajet auto</name>\n";
		$openRoute['gpxTrk'] .= "\t\t<trkseg>\n";

		// construction de la trace
		foreach ($json['features'][0]['geometry']['coordinates'] AS $wp) {
			$lat =  (string)(round((float)$wp[1]*100000)/100000);
			$lon = (string)(round((float)$wp[0]*1000000)/1000000);
			$openRoute['gpxTrk'] .= "\t\t\t<trkpt lat='".$lat."' lon='".$lon."'>\n";
			$openRoute['gpxTrk'] .= "\t\t\t</trkpt>\n";
		}
		$openRoute['gpxTrk'] .= "\t\t</trkseg>\n";
		$openRoute['gpxTrk'] .= "\t</trk>\n";
		$openRoute['gpxTrk'] .= "</gpx>";

		// construction de l'itinéraire
		// itinéraire
		$itineraire = "Départ".PHP_EOL;
		$name = "xyz";
		$desc = "xyz";
		
		
		// construction de l'itinéraire
		$distanceCumulee = 0;
		$i = 0;
		foreach ($json['features'][0]['properties']['segments'][0]['steps'] AS $rtept) {
			$instruction = (string)$rtept['instruction'];
			$instruction = str_replace("la 1er","la 1ère",$instruction);
			$instruction = str_replace("Radlofzell","Radolfzell",$instruction);
			$instruction = str_replace("Entrez le","Entrez dans le",$instruction);
			$instruction = str_replace("Tournez gauche","Tournez à gauche",$instruction);
			$instruction = str_replace("Tournez droit","Tournez à droite",$instruction);
			$instruction = str_replace("Tournez Léger","Tournez légèrement",$instruction);
			$instruction = str_replace("Tournez Pointu","Tournez fortement",$instruction);
			$instruction = str_replace("Tournez à le","Tournez au",$instruction);
			$instruction = str_replace("on the left","sur la gauche",$instruction);
			$instruction = str_replace("on the right","sur la droite",$instruction);
		

/*
			// recherche de la commune voir PB
			$lat = (string)$rtept['lat'];
			$lon = (string)$rtept['lon'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.openrouteservice.org/geocode/reverse?api_key=$cle&point.lat=$lat&point.lon=$lon&boundary.circle.radius=35");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json; charset=utf-8"
			));
			$reponse = curl_exec($ch);
			curl_close($ch);
			$locality = "";
			$trouve = FALSE;
			$j = 0;
			$rep = json_decode($reponse,TRUE);

			while (!$trouve) {
				if (isset($rep['features'][$j]['properties']['locality'])) {
					$locality = $rep['features'][$j]['properties']['locality'];
					$trouve = TRUE;
				}
				else {
					if (isset($rep['features'][$j]['properties']['localadmin'])) {
						$locality = $rep['features'][$j]['properties']['localadmin'];
						$trouve = TRUE;
					}
				}
				$j++;
				if ($j>10) $trouve = TRUE;
			}
			if ($locality!="") $locality .= " ";
*/

			$locality = " ";
			
			// formatage distance en km
			$distanceKm =number_format($distanceCumulee/1000,1,',',' ');
			while (strlen($distanceKm)<5) $distanceKm = " ".$distanceKm;
			// construction de la ligne itinéraire
			//$itineraire .=  "*$newName*!=*$name*\n";
			$itineraire .= "   ".$distanceKm." km ".$locality.$instruction.PHP_EOL;
		
			$distanceCumulee += (float)$rtept['distance'];
		}

		$itineraire .= "Arrivée au parking".PHP_EOL;

		$openRoute['itineraire'] = $itineraire;

		// modeVisualiser portrait ou paysage
		// <bounds minlat="43.227340653823525" minlon="4.990639835386869" maxlat="43.50266702851654" maxlon="5.504798453408445"/>
		
		$bbox = $json['features'][0]['bbox'];
		$minlat = (float)$bbox[1]; //   $gpxRte->rte->extensions->bounds['minlat'];
		$minlon = (float)$bbox[0]; //   $gpxRte->rte->extensions->bounds['minlon'];
		$maxlat = (float)$bbox[3]; //   $gpxRte->rte->extensions->bounds['maxlat'];
		$maxlon = (float)$bbox[2]; //   $gpxRte->rte->extensions->bounds['maxlon'];
		$horizontal = distance ($minlat, $minlon, $minlat, $maxlon);
		$vertical = distance ($minlat, $minlon, $maxlat, $minlon);
		if ($horizontal>$vertical) $openRoute['modeVisualiser'] = "paysage";
		else $openRoute['modeVisualiser'] = "portrait";
		
		return $openRoute;
	}
	// fin function openRouteService
	////////////////////////////////////////////////////////////////////////////////

	// calcul de la réponse

	$coord = $_GET['coord'];
	// sans péage
	$openRouteSansPeage = openRouteService($coord,FALSE);

	// détection d'une erreur openrouteservice
	if ($openRouteSansPeage['temps']===FALSE) $reponse = FALSE;
	else { // pas d'erreur openroute service sans péage
		$reponse['sansPeage'] = $openRouteSansPeage;		

		// avec Péage
		$openRouteAvecPeage = openRouteService($coord,TRUE);
		
		if ((float)$openRouteAvecPeage['temps']<(float)$openRouteSansPeage['temps']) {
			$reponse['avecPeage'] = $openRouteAvecPeage;		
		}
		else $reponse['avecPeage'] = FALSE;
	}
	
	// encodage json de la réponse
	$jsonRep = json_encode($reponse);
	
	// envoi de  la réponse
	header('Content-type: application/json');
	echo ($jsonRep);
	exit();
	

?>