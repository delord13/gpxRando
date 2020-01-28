<?php

////////////////////////////////////////////////////////////////////////////////
//                            gpx2tdm.inc.php
//
//                            application gpxRando
//
//    © michel delord 12/04/2012 licence logiciel libre CeCILL
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////

// Quels sont les clients ? $GLOBALS['clients'] IRLPT CPC PUBLIC
// uniquement utilisé dans ficheRandoHtml et ficheRandoPdf : à corriger
   switch ($_SERVER['SERVER_NAME']) {
		case "istresrando.lautre.net" :
			$GLOBALS['clients'] = "IRLPT";
			break;
		case "istresrando.fr" :
			$GLOBALS['clients'] = "IRLPT";
			break;
		case "gpx2tdm.free.fr" :
			$GLOBALS['clients'] = "PUBLIC";
			break;
		case "localhost": // pour l'instant en attendant l'adresse de CPC'
			$GLOBALS['clients'] = "PUBLIC";
			break;
 		case "www.cpc26120.lautre.net": // pour l'instant en attendant l'adresse de CPC'
				$GLOBALS['clients'] = "CPC";
			break;
 		case "cpc26120.lautre.net": // pour l'instant en attendant l'adresse de CPC'
				$GLOBALS['clients'] = "CPC";
			break;
	}

	
////////////////////////////////////////////////////////////////////////////////
// $GLOBALS et Constantes
////////////////////////////////////////////////////////////////////////////////

	

////////////////////////////////////////////////////////////////////////////////
// url du script d'affichage des cartes $GLOBALS['urlCarte']
////////////////////////////////////////////////////////////////////////////////
$urlCarte = "carte.php";
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// Profil de vitesse par défaut: $GLOBALS['profilVitesse']
// vous pouvez modifier ce profil de vitesse en prenant soin de ne pas modifier
// les clés (de -100 à 100 avec un pas de+2) qui représentent les % de  pente
// les valeurs sont les coefficients multiplicateurs de la vitesse à plat
////////////////////////////////////////////////////////////////////////////////
/* ancienne formule
$profilVitesse = array(
				-100=>0.1034, -98=>0.1053, -96=>0.1073, -94=>0.1094, -92=>0.1115, -90=>0.1137, -88=>0.1161, -86=>0.1185, -84=>0.1210, -82=>0.1237, -80=>0.1264, -78=>0.1293,
				-76=>0.1323, -74=>0.1355, -72=>0.1388, -70=>0.1423, -68=>0.1460, -66=>0.1499, -64=>0.1540, -62=>0.1583, -60=>0.1629, -58=>0.1677, -56=>0.1729, -54=>0.1784,
				-52=>0.1842, -50=>0.1904, -48=>0.1971, -46=>0.2043, -44=>0.2120, -42=>0.2204, -40=>0.2294, -38=>0.2394, -36=>0.2489, -34=>0.2651, -32=>0.2901, -30=>0.3227,
				-28=>0.3598, -26=>0.4005, -24=>0.4472, -22=>0.5009, -20=>0.5609, -18=>0.6257, -16=>0.6931, -14=>0.7602, -12=>0.8242, -10=>0.8821, -8=>0.9312, -6=>0.9691,
				-4=>0.9939, -2=>1.0044, 0=>1.0000, 2=>0.9742, 4=>0.9348, 6=>0.8836, 8=>0.8229, 10=>0.7554, 12=>0.6841, 14=>0.6123, 16=>0.5430, 18=>0.4790,
				20=>0.4226, 22=>0.3752, 24=>0.3371, 26=>0.3070, 28=>0.2835, 30=>0.2651, 32=>0.2509, 34=>0.2394, 36=>0.2294, 38=>0.2204, 40=>0.2120, 42=>0.2043,
				44=>0.1971, 46=>0.1904, 48=>0.1842, 50=>0.1784, 52=>0.1729, 54=>0.1677, 56=>0.1629, 58=>0.1583, 60=>0.1540, 62=>0.1499, 64=>0.1460, 66=>0.1423,
				68=>0.1388, 70=>0.1355, 72=>0.1323, 74=>0.1293, 76=>0.1264, 78=>0.1237, 80=>0.1210, 82=>0.1185, 84=>0.1161, 86=>0.1137, 88=>0.1115, 90=>0.1094,
				92=>0.1073, 94=>0.1053, 96=>0.1034, 98=>0.1016, 100=>0.0998);
*/

// Jean-Paul fontaine :
// La formule "à la mords-moi le nœud" qui donne la vitesse en fonction de la pente !
// -3167*pente^6 - 261.41*pente^5 + 813.75*pente^4 + 55.237*pente^3 - 81.669*pente^2 - 3.3689*pente + 4.4063
// Le pente étant exprimée en valeur absolue: 0.10 (pour 10%) entre -0.40 et +0.38
// transformé en coefficient multiplicateur de la vitesse à plat
$profilVitesse = array(
-100=>0.0681, -98=>0.0681, -96=>0.0681, -94=>0.0681, -92=>0.0681, -90=>0.0681, -88=>0.0681, -86=>0.0681, -84=>0.0681, -82=>0.0681, -80=>0.0681, -78=>0.0681, -76=>0.0681, -74=>0.0681, -72=>0.0681, -70=>0.0681, -68=>0.0681, -66=>0.0681, -64=>0.0681, -62=>0.0681, -60=>0.0681, -58=>0.0681, -56=>0.0681, -54=>0.0681, -52=>0.0681, -50=>0.0681, -48=>0.0681, -46=>0.0681, -44=>0.0681, -42=>0.0681, -40=>0.0681, -38=>0.0778, -36=>0.1733, -34=>0.2375, -32=>0.2838, -30=>0.3219, -28=>0.3591, -26=>0.3998, -24=>0.4466, -22=>0.5003, -20=>0.5604, -18=>0.6253, -16=>0.6927, -14=>0.7599, -12=>0.824, -10=>0.882, -8=>0.9311, -6=>0.9691, -4=>0.9939, -2=>1.0044, 0=>1, 2=>0.9742, 4=>0.9348, 6=>0.8835, 8=>0.8227, 10=>0.7551, 12=>0.6837, 14=>0.6118, 16=>0.5424, 18=>0.4784, 20=>0.4219, 22=>0.3745, 24=>0.3363, 26=>0.3062, 28=>0.2809, 30=>0.2553, 32=>0.2214, 34=>0.1681, 36=>0.081, 38=>0.0681, 40=>0.0681, 42=>0.0681, 44=>0.0681, 46=>0.0681, 48=>0.0681, 50=>0.0681, 52=>0.0681, 54=>0.0681, 56=>0.0681, 58=>0.0681, 60=>0.0681, 62=>0.0681, 64=>0.0681, 66=>0.0681, 68=>0.0681, 70=>0.0681, 72=>0.0681, 74=>0.0681, 76=>0.0681, 78=>0.0681, 80=>0.0681, 82=>0.0681, 84=>0.0681, 86=>0.0681, 88=>0.0681, 90=>0.0681, 92=>0.0681, 94=>0.0681, 96=>0.0681, 98=>0.0681, 100=>0.0681
);
////////////////////////////////////////////////////////////////////////////////
// Textes affichés et info-bulles : $GLOBALS['txt']
////////////////////////////////////////////////////////////////////////////////
		$txt['menuTdm'] ['texte'] = "TDM";
		$txt['menuTdm'] ['title'] = "Menu Tableau De Marche";
		$txt['menuGpx'] ['texte'] = "GPX";
		$txt['menuGpx'] ['title'] = "Menu GPX trace et points de passage";
		$txt['menuFicheRando'] ['texte'] = "Fiche-Rando";
		$txt['menuFicheRando'] ['title'] = "Menu Fiche-Rando";
		$txt['menuCarte'] ['texte'] = "Carte";
		$txt['menuCarte'] ['title'] = "Menu Carte";
		
		$txt['parametres'] ['texte'] = "Paramètres du tableau de marche";
		$txt['colonnes'] ['texte'] = "Colonnes";
		$txt['synthese'] ['texte'] = "Synthèse";
		$txt['profil'] ['texte'] = "Profil";
		$txt['ficheRando'] ['texte'] = "Paramètres de la fiche-rando";

		// menu Tdm
		$txt['enregistreTdm'] ['texte'] = "Enregistrer le TDM";
		$txt['enregistreTdm'] ['title'] = "propose d'enregistrer ce tableau de marche (avec la fiche rando, la trace et les points de passage) sous forme d'un fichier tdm afin de pouvoir l'ouvrir ultérieurement";

		$txt['imprimeTdm'] ['texte'] = "Imprimer le TDM et le profil";
		$txt['imprimeTdm'] ['title'] = "recalcule ce tableau de marche et génère un tableau de marche et un profil dans un nouvel onglet ; vous pouvez l'imprimer à partir de la commande Fichier/Imprimer du navigateur en choisissant le format paysage étant donnée la largeur du tableau.";

		$txt['envoyerTdmCsv'] ['texte'] = "Enregistrer le TDM en CSV";
		$txt['envoyerTdmCsv'] ['title'] = "propose d'enregistrer ce tableau de marche  sous forme d'un fichier CSV afin de pouvoir l'ouvrir avec un tableur";

		$txt['recalcul'] ['texte'] = "Recalculer le TDM"; // &#x263C; 
		$txt['recalcul'] ['title'] = "recalcule le tableau de marche et met à jour la fiche-rando et le profil pour prendre en compte les modifications effectuées";

		$txt['quitter'] ['texte'] = "Quitter";  
		$txt['quitter'] ['title'] = "quitte ce tableau de marche et retourne au menu principal de gpxRando";


		
		// menu Gpx 
		$txt['creeWptTrk'] ['texte'] = "Enregistrer la trace et les points de passage";
		$txt['creeWptTrk'] ['title'] = "propose d'enregistrer un fichier gpx contenant les points de passages (wpt) et la trace (trk)";

		$txt['creeWpt'] ['texte'] = "Enregistrer les points de passage";
		$txt['creeWpt'] ['title'] = "propose d'enregistrer un fichier gpx contenant les points de passages";

		$txt['creeWptTrkLisse'] ['texte'] = "Enregistrer la trace lissée et les points de passage";
		$txt['creeWptTrkLisse'] ['title'] = "propose d'enregistrer un fichier gpx contenant les points de passages (wpt) et la trace (trk) lissée";

		$txt['analyse'] ['texte'] = "Analyser la trace";
		$txt['analyse'] ['title'] = "propose d'afficher ou d'enregistrer une page d'analyse de la trace : distances, dénivelées et pentes entre les points de trace";
=======
>>>>>>> 4d147fc9eecc88b6d579f6e5fd8bdce41cc3ad72

		
		// menu Fiche-Rando
		$txt['ficheHtml'] ['texte'] = "Fiche-rando HTML";
		$txt['ficheHtml'] ['title'] = "recalcule ce tableau de marche, met en forme la fiche-rando et génère la fiche-rando dans un nouvel onglet.";

//		$txt['imprimeFiche'] ['texte'] = "Fiche-rando en pdf";
		$txt['fichePdf'] ['texte'] = "Fiche-rando PDF";
		$txt['fichePdf'] ['title'] = "recalcule ce tableau de marche, met en forme la fiche-rando et génère la fiche-rando en pdf ; vous pouvez l'ouvrir, l'enregistrer, l'imprimer.";
		
		
		// menu Carte
		$txt['carteAuto'] ['texte'] = "Carte format automatique";
		$txt['carteAuto'] ['title'] = "affiche dans un nouvel onglet la trace et les points de passage sur la carte IGN au format 'portrait' ou 'paysage' selon la géométrie de la trace ; il est conseillé d'utiliser l'extension 'Page Saver WE' de Firefox qui permet de capturer et l'enregistrer l'image de la page entière ; la page est formatée pour être imprimable en A4.";


		$txt['cartePortrait'] ['texte'] = "Carte format portrait";
		$txt['cartePortrait'] ['title'] = "affiche la trace et les points de passage sur la carte IGN au format 'portrait' dans un nouvel onglet ; il est conseillé d'utiliser l'extension 'Page Saver WE' de Firefox qui permet de capturer et l'enregistrer l'image de la page entière ; la page est formatée pour être imprimable en A4.";

		$txt['cartePaysage'] ['texte'] = "Carte format paysage";
		$txt['cartePaysage'] ['title'] = "affiche la trace et les points de passage sur la carte IGN au format 'paysage' dans un nouvel onglet ; il est conseillé d'utiliser l'extension 'Page Saver WE' de Firefox qui permet de capturer et l'enregistrer l'image de la page entière ; la page est formatée pour être imprimable en A4";
		
		
		
      // onglets
		$txt['onglets'] ['texte'] = "Onglets";
		$txt['onglets'] ['title'] = "Choix de l'onglet'";

		$txt['ongletTdm'] ['texte'] = "Tableau de marche";
		$txt['ongletTdm'] ['title'] = "affiche le tableau de marche de la rando";

		$txt['ongletProfil'] ['texte'] = "Profil";
		$txt['ongletProfil'] ['title'] = "affiche le profil de la rando";

		$txt['ongletFiche'] ['texte'] = "Fiche-rando";
		$txt['ongletFiche'] ['title'] = "affiche les paramètres de la fiche de la rando";

		$txt['ongletAide'] ['texte'] = "Aide";
		$txt['ongletAide'] ['title'] = "affiche l'aide de gpx2tdm";





//paramètres
		$txt['nomRando'] ['texte'] = "Nom du fichier";
		$txt['nomRando'] ['title'] = "Nom du fichier tdm";

		$txt['date'] ['texte'] = "date";
		$txt['date'] ['title'] = "date de la randonnée ; cliquez pour choisir dans le calendrier";

		$txt['animateur'] ['texte'] = "animateur";
		$txt['animateur'] ['title'] = "nom(s) du ou des animateur(s)";

		$txt['vitesse'] ['texte'] = "vitesse à plat (km/h)";
		$txt['vitesse'] ['title'] = "vitesse à plat en km/h : utilisée pour calculer les temps de marche";

		$txt['methodeEffort'] ['texte'] = "dénivelée => m-effort";
		$txt['methodeEffort'] ['title'] = "méthode de calcul du temps de marche : la dénivelée multipliée par le coefficient est ajoutée à la distance pour le calcul du temps de marche";

		$txt['methodeEffortObligatoire'] ['texte'] = "Le temps de marche est calculé selon la méthode du \"mètre-effort\"<br>(dite aussi \"méthode suisse\") pour prendre en compte les dénivelées :<br>on ajoute à la distance à plat 10 fois la dénivelée positive.";


		$txt['coefPos'] ['texte'] = "coeff déniv positive";
		$txt['coefPos'] ['title'] = "coeff déniv positive = n : 100 mètres de dénivellée positive ajoutent n fois le temps pour 100 mètres à plat";

		$txt['coefNeg'] ['texte'] = "coeff déniv négative";
		$txt['coefNeg'] ['title'] = "coeff déniv négative = n : 100 mètres de dénivellée négative ajoutent n fois le temps pour 100 mètres à plat";

		$txt['methodeProfil'] ['texte'] = "profil de vitesse selon la pente";
		$txt['methodeProfil'] ['title'] = "méthode de calcul du temps de marche : un profil de vitesse selon la pente permet de calculer le temps de marche ; l'effet de la pente sur la vitesse est paramétrable";

		$txt['kPos'] ['texte'] = "effet en montée";
		$txt['kPos'] ['title'] = "modifie l'effet de la pente sur la vitesse en montée : 1 par défaut ;  0.1=effet minimum; 1.9=effet maximum";

		$txt['kNeg'] ['texte'] = "effet en descente";
		$txt['kNeg'] ['title'] = "modifie l'effet de la pente sur la vitesse en descente : 1 par défaut ;  0.1=effet minimum; 1.9=effet maximum";

		$txt['parametresNB'] ['texte'] = "NB Recalcul";
		$txt['parametresNB'] ['title'] = "après des modifications des champs 'vitesse à plat', 'coeff déniv positive/négative', 'effet en montée/en descente', 'mode de calcul des dénivelées', 'lissage des altitudes', 'seuil de dénivelée', la mise à jour du tableau de marche est automatique.";

		$txt['calcDeniv'] ['texte'] = "dénivelées calculées à partir des";
		$txt['calcDeniv'] ['title'] = "mode de calcul des dénivelées";

		$txt['pointsPassage'] ['texte'] = "points de passage";
		$txt['pointsPassage'] ['title'] = "calcul des dénivelées à partir des points de passage";

		$txt['pointsTrace'] ['texte'] = "points de trace";
		$txt['pointsTrace'] ['title'] = "calcul des dénivelées à partir des points de trace ; attention ce mode de calcul a tendance à suestimer la dénivellée car les imprécisions de mesure d'altitude se cumulent ; il peut être judicieux de travailler sur des moyennes et/ou de fixer un seuil pour la prise en compte de la dénivelée.";

		$txt['distanceLissage'] ['texte'] = "lissage des altitudes";
		$txt['distanceLissage'] ['title'] = "remplace l'altitude de chaque point de trace par la moyenne des altitudes des points de trace situés sur un segment de la distance choisie dont il est le centre";

		$txt['seuil'] ['texte'] = "seuil de dénivelée";
		$txt['seuil'] ['title'] = "ne prend en compte la denivelée qu'à partir d'un certain seuil en mètres";

//colonnes

		$txt['de'] ['texte'] = "de";
		$txt['de'] ['title'] = "de : point de passage précédent (point de départ de l'étape)";

		$txt['a'] ['texte'] = "à";
		$txt['a'] ['title'] = "à : point de passage courant (point d'arrivée de l'étape)";

		$txt['name'] ['texte'] = "point de passage";
		$txt['name'] ['title'] = "intitulé du point de passage courant : peut être modifié";

		$txt['utm'] ['texte'] = "UTM<br>WGS84";
		$txt['utm'] ['title'] = "coordonnées UTM WGS84 compatible avec IGN RGF93 : Zone Est UTM Nord en km";

		$txt['azimut'] ['texte'] = "azimut<br>(°)";
		$txt['azimut'] ['title'] = "azimut calculé entre le point de départ et le point de trace suivant";

		$txt['ele'] ['texte'] = "altitude<br>(m)";
		$txt['ele'] ['title'] = "élévation du point d'arrivée : peut être modifiée";

		$txt['distance'] ['texte'] = "distance<br>(m)";
		$txt['distance'] ['title'] = "distance entre le point d'arrivée et le point de départ de l'étape";

		$txt['distanceCumul'] ['texte'] = "distance<br>cumul.<br>(m)";
		$txt['distanceCumul'] ['title'] = "distance totale jusqu'au point d'arrivée de l'étape";

		$txt['deniv'] ['texte'] = "dénivelée<br>(m)";
		$txt['deniv'] ['title'] = "différence d'altitude entre le point d'arrivée et le point de départ de l'étape";

		$txt['denivPos'] ['texte'] = "dénivelée<br>pos. (m)";
		$txt['denivPos'] ['title'] = "cumul des différences positives d'altitude entre les points de trace de l'étape";

		$txt['denivNeg'] ['texte'] = "dénivelée<br>nég. (m)";
		$txt['denivNeg'] ['title'] = "cumul des différences négatives d'altitude entre les points de trace de l'étape";

		$txt['denivBrutePos'] ['texte'] = "dénivelée<br>positive (m)";
		$txt['deniv'] ['title'] = "cumul des différences d'altitude positives entre les points de trace";

		$txt['denivBruteNeg'] ['texte'] = "dénivelée<br>négative (m)";
		$txt['deniv'] ['title'] = "cumul des différences d'altitude négatives entre les points de trace";

		$txt['pente'] ['texte'] = "pente";
		$txt['pente'] ['title'] = "dénivelée divisée par distance en %";

		$txt['pentePos'] ['texte'] = "pente pos.";
		$txt['pentePos'] ['title'] = "dénivelée positive de l'étape divisée par la distance en montée (en %)";

		$txt['penteNeg'] ['texte'] = "pente nég.";
		$txt['penteNeg'] ['title'] = "dénivelée négative de l'étape divisée par la distance en descente (en %)";

		$txt['denivAdd'] ['texte'] = "dénivelée<br>add.<br>(m)";
		$txt['denivAdd'] ['title'] = "dénivelée additionnelle : vous pouvez ajouter une dénivelée pour tenir compte du fait qu'un segment à dénivelée positive peut contenir des descentes et remontées et qu'un segment à dénivelée négative peut contenir des montées et redescentes ; cette dénivelée additionnelle sera ajoutée à la fois en positif et en négatif pour le calcul du temps de marche et pour le calcul des dénivelées cumulées ; cette colonne est cachée quand on choisit l'option 'dénivelées calculées à partir des points de trace'.";

		$txt['dureeAdd'] ['texte'] = "tps add.<br>(min)";
		$txt['dureeAdd'] ['title'] = "temps de marche additionnel : à ajouter pour tenir compte des ralentissements dûs à des passages difficiles";

		$txt['duree'] ['texte'] = "tps de<br>marche<br>(min)";
		$txt['duree'] ['title'] = "temps de marche : calculé en fonction de la vitesse à plat et de la méthode choisie (kilomètre-effort ou profil de vitesse selon la pente)";

		$txt['pause'] ['texte'] = "pause<br>(min)";
		$txt['pause'] ['title'] = "temps de pause au point d'arrivée (pique-nique par exemple); il n'est pas comptabilisé dans le temps de marche mais se répercute sur l'heure d'arrivée à l'étape suivante";

		$txt['heureArrivee'] ['texte'] = "heure<br>d'arrivée";
		$txt['heureArrivee'] ['title'] = "heure d'arrivée en fonction de l'heure d'arrivée au point de départ, des temps de marche cumulés, des temps de marche additionnels cumulés et des pauses.";

		$txt['observ'] ['texte'] = "observations";
		$txt['observ'] ['title'] = "vous pouvez noter ici vos remarques sur l'étape";

		$txt['colonnesNB'] ['texte'] = "NB Recalcul";
		$txt['colonnesNB'] ['title'] = "après des modifications des champs 'altitude', 'dénivelée add', 'tps add', 'pause', 'heure d'arrivée', il faut cliquer sur 'Recalculer' pour mettre à jour le tableau de marche.";

//synthèse
		$txt['distanceTotale'] ['texte'] = "distance totale";
		$txt['distanceTotale'] ['title'] = "longueur de la rando en km";

		$txt['dureeTotale'] ['texte'] = "temps de marche total";
		$txt['dureeTotale'] ['title'] = "temps de marche total (hors pauses)";

		$txt['denivTotPos'] ['texte'] = "dénivelée positive cumulée";
		$txt['denivTotPos'] ['title'] = "cumul des différences positives d'altitude";

		$txt['denivTotNeg'] ['texte'] = "dénivelée négative cumulée";
		$txt['denivTotNeg'] ['title'] = "cumul des différences négatives d'altitude";

		$txt['altitudeMin'] ['texte'] = "altitude minimum";
		$txt['altitudeMin'] ['title'] = "altitude minimum de la trace";

		$txt['altitudeMax'] ['texte'] = "altitude maximum";
		$txt['altitudeMax'] ['title'] = "altitude maximum de la trace";

		$txt['nbMaxTrkpt'] ['texte'] = "Nombre maximum de points de trace par km";
		$txt['nbMaxTrkpt'] ['title'] = "Réduction de la trace : nombre maximum de points de trace par km";

		$txt['nbTrkpt'] ['texte'] = "nombre de points de trace";
		$txt['nbTrkpt'] ['title'] = "nombre de points de trace de la rando";
		

		$txt['denivBrutePos'] ['texte'] = "dénivelée positive cumulée (points de trace)";
		$txt['denivBrutePos'] ['title'] = "cumul des différences positives d'altitude entre les points de trace";

		$txt['denivBruteNeg'] ['texte'] = "dénivelée négative cumulée (points de trace)";
		$txt['denivBruteNeg'] ['title'] = "cumul des différences négatives d'altitude entre les points de trace";


//profil
		$txt['segments'] ['texte'] = "segments en rouge";
		$txt['segments'] ['title'] = "profil des points de passage";

		$txt['points'] ['texte'] = "segments en vert";
		$txt['points'] ['title'] = "profil des points de trace";
		
// fiche rando
		$txt['ficheRando']['consignes']['0'] = "La commande 'Imprimer la fiche-rando' génère une page dans un autre onglet en fonction des paramètres de cette section ; vous pouvez l'imprimer à partir de la commande Fichier/Imprimer du navigateur.";
		$txt['ficheRando']['consignes']['1'] = "Les rubriques non renseignées seront ignorées lors de la création de la fiche-rando.";
		$txt['ficheRando']['consignes']['2'] = "Le logo (fichier image) sera placé en haut à gauche de la fiche-rando.";
		$txt['ficheRando']['consignes']['3'] = "Vous avez la possibilité de modifier le 'Titre' et les résultats calculés dans le tableau de marche : 'Denivelée positive cumulée', 'Denivelée négative cumulée', 'Longueur' et 'Temps de marche'.";
		
		$txt['ficheRando']['logoUrl']['id'] = "Logo";
		$txt['ficheRando']['logoUrl']['autre'] = "choisir un fichier image comme logo";
		$txt['ficheRando']['logoUrl']['value'] = LOGO_URL;
		
		$txt['ficheRando']['titre']['id'] = "Nom du fichier (TDM)";
		$txt['ficheRando']['titre']['value'] = "";
		
		$txt['ficheRando']['titreFiche']['id'] = "Titre de la fiche";
		$txt['ficheRando']['titreFiche']['value'] = "";
		
		$txt['ficheRando']['sousTitre']['id'] = "Sous-titre";
		$txt['ficheRando']['sousTitre']['value'] = "";
		
		$txt['ficheRando']['date']['id'] = "Date";
		$txt['ficheRando']['date']['value'] = "";
		
		$txt['ficheRando']['presentation']['id'] = "Présentation";
		$txt['ficheRando']['presentation']['value'] = "";
		
		$txt['ficheRando']['remarques']['id'] = "Remarques";
		$txt['ficheRando']['remarques']['value'] = "";

		$txt['ficheRando']['niveau']['id'] = "Niveau";
		$txt['ficheRando']['niveau']['value'] = "";
		
		$txt['ficheRando']['nbParticipants']['id'] = "Nb participants";
		$txt['ficheRando']['nbParticipants']['value'] = "";

		if (IBP) {
			$txt['ficheRando']['ibpIndex']['id'] = "Indice IBP";
			$txt['ficheRando']['ibpIndex']['value'] = IBP_TXT;
		}
		
		if (THEME) {
			$txt['ficheRando']['themeCode']['id'] = "Thème";
			$txt['ficheRando']['themeCode']['value'] = "";
			
			$txt['ficheRando']['themeDescription']['id'] = "Description du thème";
			$txt['ficheRando']['themeDescription']['value'] = "";
		}
		
		$txt['ficheRando']['denivPos']['id'] = "Dénivelée positive cumulée TDM";
		$txt['ficheRando']['denivPos']['value'] = "";
		
		$txt['ficheRando']['denivPosFiche']['id'] = "Dénivelée positive cumulée";
		$txt['ficheRando']['denivPosFiche']['value'] = "";

		$txt['ficheRando']['denivNeg']['id'] = "Dénivelée négative cumulée TDM";
		$txt['ficheRando']['denivNeg']['value'] = "";
		
		$txt['ficheRando']['denivNegFiche']['id'] = "Dénivelée négative cumulée";
		$txt['ficheRando']['denivNegFiche']['value'] = "";
		
		$txt['ficheRando']['longueur']['id'] = "Longueur TDM";
		$txt['ficheRando']['longueur']['value'] = "";
		
		$txt['ficheRando']['longueurFiche']['id'] = "Longueur";
		$txt['ficheRando']['longueurFiche']['value'] = "";
		
		$txt['ficheRando']['duree']['id'] = "Temps de marche TDM";
		$txt['ficheRando']['duree']['value'] = "";
		
		$txt['ficheRando']['dureeFiche']['id'] = "Temps de marche";
		$txt['ficheRando']['dureeFiche']['value'] = "";
		
		$txt['ficheRando']['difficultes']['id'] = "Difficultés";
		$txt['ficheRando']['difficultes']['value'] = DIFFICULTES;
		
		$txt['ficheRando']['carte']['id'] = "Carte";
		$txt['ficheRando']['carte']['value'] = CARTE;
		
		$txt['ficheRando']['rdv']['id'] = "RDV";
		$txt['ficheRando']['rdv']['value'] = RDV;
		
		$txt['ficheRando']['depart']['id'] = "Départ à";
		$txt['ficheRando']['depart']['value'] = DEPART;
		
		if (OPENSERVICE_ROUTE) {
			$txt['ficheRando']['trajet']['id'] = "Itinéraire(s) routiers suggéré(s) détaillé(s)";
			$txt['ficheRando']['trajet']['value'] = TRAJET;
		}
		else {
			$txt['ficheRando']['trajet']['id'] = "Itinéraire routier suggéré";
			$txt['ficheRando']['trajet']['value'] = TRAJET;
		}
		
		$txt['ficheRando']['parking']['id'] = "Parking";
		$txt['ficheRando']['parking']['value'] = "";
		
		$txt['ficheRando']['trajetKm']['id'] = "Trajet routier AR en km";
		$txt['ficheRando']['trajetKm']['value'] = "";
		
		$txt['ficheRando']['covoiturage']['id'] = "Covoiturage";
		$txt['ficheRando']['covoiturage']['value'] = "";
		
	
		
		$txt['ficheRando']['laRandonnee']['id'] = "La randonnée";
		$txt['ficheRando']['laRandonnee']['value'] = "";
		
		$txt['ficheRando']['equipement']['id'] = "Équipement";
		$txt['ficheRando']['equipement']['value'] = EQUIPEMENT;
		
		$txt['ficheRando']['animateur']['id'] = "Animateur";
		$txt['ficheRando']['animateur']['value'] = "";
		
		$txt['ficheRando']['complementsTitre']['id'] = "Titre des compléments";
		$txt['ficheRando']['complementsTitre']['value'] = "Compléments";
		
		$txt['ficheRando']['complements']['id'] = "Compléments";
		$txt['ficheRando']['complements']['value'] = "";
		



////////////////////////////////////////////////////////////////////////////////
// IBP Index
////////////////////////////////////////////////////////////////////////////////

/*

// Pour utiliser IBPIndex
/*
USAGE DE LA CLASSE
   // fichier gpx de la trace
   $nomFichierGpx = "Villars-GrandsClementsCombeStPierre2018_wpt_trk.gpx";
   $cheminWebFichierGpx = "temp/".$nomFichierGpx;
   $cheminReelFichierGpx = realpath($cheminWebFichierGpx);
   // objet curl file application/gpx+xml
   $objCurlFile = curl_file_create ($cheminReelFichierGpx, "application/gpx+xm", $nomFichierGpx);
   // instanciation = appel de l"API
   $ibp = new IBP($objCurlFile);
   // récupération de la réponse json
   $ibpindex_json = $ibp->ibp;
   // décodage de la réponse json
   $reponse = json_decode($ibpindex_json, true);
   // extraction de l'index'
   $ibpIndex = $reponse["hiking"]["ibp"];

*/
   class IBP {
      var $filename; //Source filename
      var $ibp; //Resoult: JSON Object)
      function __construct($filename = false){ //Constructor
         if(!empty($filename)) $this->getIBP($filename);
      }
      function getIBP($filename) {
               //Post fields
               $post_data = array();
               $post_data['file'] = $filename;
               $post_data['key'] = CLE_IBPINDEX;  // My api key
               //Curl connection
               $ch = curl_init();

               curl_setopt($ch, CURLOPT_URL, "https://www.ibpindex.com/api/" );  // or "http://www.ibpindex.com/api/index.php"
               curl_setopt($ch, CURLOPT_POST, TRUE );
               curl_setopt($ch, CURLOPT_HEADER, FALSE);
               curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
               
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000); // max 2 secondes

               $postResult = curl_exec($ch); //return result
//$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);               
//die("code : $code");

              
               if (curl_errno($ch)) {
                  curl_close($ch); //close connection
                  return "";
                 // die("erreur CURL : ".curl_error($ch)); //this stops the execution under a Curl failure
               }
               
               curl_close($ch); //close connection
               $this->ibp = $postResult;
               return $postResult; 
      }
   }
// fin class IBP

function indiceIbpIndex($nomFichierGpx, $cheminWebFichierGpx) {
//return "non disponible";
	$cheminReelFichierGpx = realpath($cheminWebFichierGpx);
	$objCurlFile = curl_file_create ("$cheminWebFichierGpx", "application/gpx+xm", $nomFichierGpx);
   // instanciation = appel de l"API
   $ibp = new IBP($objCurlFile);
   // récupération de la réponse json
   $ibpindex_json = $ibp->ibp;
   // décodage de la réponse json
   $reponse = json_decode($ibpindex_json, true);
//var_dump($reponse);die();
   // extraction de l'index'
   $ibpIndex = $reponse["hiking"]["ibp"];
	return $ibpIndex;
}


////////////////////////////////////////////////////////////////////////////////
// fonction de correction des URL dans les sessions
// elle rend une URL complétée avec l'identifiant de session dans le cas où
// le navigateur du client n'a pas envoyé de cookie de session
////////////////////////////////////////////////////////////////////////////////
function session_url($url) {

   if (!isset($_COOKIE[session_name()])) {   // && ini_get("session.use_trans_sid")==0
        if (strpos($url, "?") !=0) return $url."&".SID;
        else return $url."?".SID;
   }
   else return $url;
}
function fin_session_url() {
   if (!isset($_COOKIE[session_name()])) {   // && ini_get("session.use_trans_sid")==0
        return "?".SID;
   }
   else return "";
}

////////////////////////////////////////////////////////////////////////////////

?>
