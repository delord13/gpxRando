<?php

////////////////////////////////////////////////////////////////////////////////
//                          profil.php
//                          application gpx2tdm 
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////
//	création de l'image profil de la rando

////////////////////////////////////////////////////////////////////////////////

//header ("Content-type: image/png");

// session et include
require '../inc/sessionsMultiplesSousAppli.inc.php';
//session_start();
require '../inc/config.inc.php';
require '../inc/gpx2tdm.inc.php';

// chemin des fichiers ttf pour GD v2
$chemin = realpath('.')."/";
$dejaVuSans = $chemin . "DejaVuSans.ttf";
$dejaVuSansMono = $chemin . "DejaVuSansMono.ttf";

// paramétrage du graphique
//$taille = 57; // 40 nb de pixel pour 100m dénivelée et 1km distance
$margeHaut = 80;
$margeBas = 80;
$margeDroite = 40;
$margeGauche = 80;

// détermination de la taille de l'image


// ele en hm (calculé sur les points de trace
$eleMin = 100;
$eleMax = -10;
foreach ($_SESSION['trk'] as $i => $unTrkpt ) {
	$unEle = $unTrkpt['ele'];
	if ($unEle>-32767) {
		if ($eleMin>$unEle/100) $eleMin=$unEle/100;
		if ($eleMax<$unEle/100) $eleMax=$unEle/100;
	}
}
/*
$eleMin = 100;
$eleMax = -10;
foreach($_SESSION['pdp'] as $i =>$pdp) {
	$unEle = $pdp['ele'];
	if ($unEle>-32767) {
		if ($eleMin>$unEle/100) $eleMin=$unEle/100;
		if ($eleMax<$unEle/100) $eleMax=$unEle/100;
	}
}
*/
$eleMin = floor($eleMin);
$eleMax = floor($eleMax)+1;
// dist en km
$distMin = 1;
$n = count($_SESSION['pdp'])-1;
$distMax = floor($_SESSION['pdp'][$n]['distanceCumul']/1000) + 1;

$nbKm = $distMax;
$nbHm = $eleMax-$eleMin+1; //$eleMax-$eleMin

// taille en pixel de 1km et 100m de telle sorte que l'image tienne sur A4 paysage
$tailleX = floor(855/($nbKm+1)); //855
$tailleY = floor(460/($nbHm+1)); //480

$taille = min($tailleX,$tailleY);

/*
// changement de taille pour les petits profils
if ($nbKm<10 and $nbHm<10) $taille = 80;
*/
$largeur = $margeGauche+($nbKm+1)*$taille+$margeDroite;
$hauteur = $margeHaut+($nbHm+1)*$taille+$margeBas;


// coordonnées de l'origine des axes
$x0 = $margeGauche;
$y0 = $margeHaut+($nbHm+1)*$taille;

// création de l'image
// couleur de fond : jaune pâle (255, 255, 128)
// jeu de couleur ; le premier est la couleur de fond
$image = imagecreate($largeur,$hauteur);
//$jauneClair = imagecolorallocate($image, 255, 255, 128);
$blanc = imagecolorallocate($image, 255, 255, 255);
$bleuClair = imagecolorallocate($image, 128, 255, 255);
$bleu = imagecolorallocate($image, 0, 0, 255);
$orange = imagecolorallocate($image, 255, 128, 0);
$noir = imagecolorallocate($image, 0, 0, 0);
$rouge = imagecolorallocate($image, 255, 0, 0);
$grisClair = imagecolorallocate($image, 192, 192, 192);
$vert = imagecolorallocate($image, 0, 153, 0);
$vertClair = imagecolorallocate($image, 51, 255, 51);


// dessin de l'axe des abscisses (distance en km) 
imageline($image, $x0, $y0, $x0+($nbKm+1)*$taille, $y0, $noir);
// gradué par 1 km
for ($km=0; $km<=$nbKm; $km++) {
	imageline($image, $x0+$km*$taille, $y0-2, $x0+$km*$taille, $y0+2, $noir);
	// étiquettes des km
	$decalage = -4;
	if ($km>9) $decalage -= 4;
	imagettftext (  $image , 10 , 0 , $x0+$km*$taille+$decalage , $y0+24 , $noir , $dejaVuSansMono , (string) $km );	
}
// km
imagettftext (  $image , 10 , 0 , $x0+$km*$taille-120 , $y0+48 , $noir , $dejaVuSansMono , "distance en km" );	

// flèche
imageline($image, $x0+($nbKm+1)*$taille-5, $y0-3, $x0+($nbKm+1)*$taille, $y0, $noir);
imageline($image, $x0+($nbKm+1)*$taille-5, $y0+3, $x0+($nbKm+1)*$taille, $y0, $noir);


// dessin de l'axe des ordonnées (dénivelée en m) gradué par 100 m
imageline($image,$x0, $y0,  $x0, $y0-$nbHm*$taille-$taille, $noir);
// gradué par 1 hm
for ($hm=0; $hm<=$nbHm; $hm++) {
	imageline($image, $x0-2, $y0-$hm*$taille, $x0+2, $y0-$hm*$taille, $noir);
	// étiquettes des 100m
	$etiq = ($hm+$eleMin)*100;
	$decalage = 0;
	if ($etiq<1000) $decalage += 8;
	if ($etiq<100) $decalage += 8;
	if ($etiq<10) $decalage += 8;
	imagettftext (  $image , 10 , 0 , $x0-40-10+$decalage , $y0-$hm*$taille+4 , $noir , $dejaVuSansMono , (string) $etiq );	
}

// m
imagettftext (  $image , 10 , 0 , $x0-40-10 , $y0-$hm*$taille-10 , $noir , $dejaVuSansMono , "altitude en m" );	

// flèche
imageline($image, $x0-3, $y0-$hm*$taille+5, $x0, $y0-$hm*$taille, $noir);
imageline($image, $x0+3, $y0-$hm*$taille+5, $x0, $y0-$hm*$taille, $noir);



// dessin du quadrillage en bleu clair (128, 255, 255)
// horizontal
for ($hm=1; $hm<=$nbHm; $hm++) {
	imageline($image, $x0+2, $y0-$hm*$taille, $x0+$nbKm*$taille+$taille-5, $y0-$hm*$taille, $grisClair);
}
// vertical
for ($km=1; $km<=$nbKm; $km++) {
	imageline($image,$x0+$km*$taille, $y0-2,  $x0+$km*$taille, $y0-$nbHm*$taille-$taille+5, $grisClair);
}

// dessin du profil des points de passage : segments en rouge, points et étiquettes
foreach ($_SESSION['pdp'] as $i => $unPdp) {
	$name = $unPdp['name'];
	if ($i>0 AND $unPdp['ele']!=-32767) {
		$j = $i-1;
		while ($_SESSION['pdp'][$j]['ele']==-32767 AND $j!=0) $j--;
		$dist1 = $_SESSION['pdp'][$j]['distanceCumul'];
		$dist2 = $unPdp['distanceCumul'];
		$ele1 = $_SESSION['pdp'][$j]['ele'];
		$ele2 = $unPdp['ele'];
		$x1 = $x0+round($dist1*$taille/1000,0);
		$y1 = $y0-round(($ele1/100-$eleMin)*$taille,0);
		$x2 = $x0+round(+$dist2*$taille/1000,0);
		$y2 = $y0-round(($ele2/100-$eleMin)*$taille,0);
		// segment
		imageline($image,$x1,$y1,$x2,$y2,$rouge);
		// croix
		imageline($image,$x2-2,$y2-2,$x2+2,$y2+2,$noir);
		imageline($image,$x2-2,$y2+2,$x2+2,$y2-2,$noir);
		// étiquettes
/*		$point = chr(65+$i%26);
		if ($i>=26) $point = $point . floor(($i+1)/26);
		imagettftext (  $image , 8 , 70 , $x2 , $y2-7 , $noir , $dejaVuSansMono , $point."-".$name );
*/
		imagettftext (  $image , 8 , 70 , $x2 , $y2-7 , $noir , $dejaVuSansMono , htmlspecialchars_decode($name,ENT_QUOTES) );
//		imagettftext (  $image , 10 , 0 , $x2-4 , $y2-7 , $noir , $dejaVuSansMono , $point );
		// point de départ : étiquette et croix
		if ($j==0) {
			// croix
			imageline($image,$x1-2,$y1-2,$x1+2,$y1+2,$noir);
			imageline($image,$x1-2,$y1+2,$x1+2,$y1-2,$noir);
			// étiquette
			$name = $_SESSION['pdp'][$j]['name'];
			imagettftext (  $image , 8 , 70 , $x1-4 , $y1-7 , $noir , $dejaVuSansMono , htmlspecialchars_decode($name,ENT_QUOTES) );
		}
	}
}

// dessin du profil des trkpt : points en vert en sautant les ele<-100 (=-32767)
$x = $x0;
$distCumul = 0;
$distPrec = 0;
$elePrec = $_SESSION['trk'][0]['ele'];
foreach ($_SESSION['trk'] as $i => $unTrkpt ) {
	if ($i>0) {
		$distCumul += $unTrkpt['distance'];
		if ($unTrkpt['ele']>-1000) {
			$dist1 = $distPrec;
			$dist2 = $distCumul;
			$ele1 = $elePrec;
			$ele2 = $unTrkpt['ele'];
			$x1 = $x0+round($dist1*$taille/1000,0);
			$y1 = $y0-round(($ele1/100-$eleMin)*$taille,0);
			$x2 = $x0+round(+$dist2*$taille/1000,0);
			$y2 = $y0-round(($ele2/100-$eleMin)*$taille,0);
			// segment
			imageline($image,$x1,$y1,$x2,$y2,$vert);
			$distPrec = $dist2;
			$elePrec = $ele2;
		}

	}
}


// titre du profil
imagettftext (  $image , 11 , 0 , 30 , 25 , $noir , $dejaVuSans , "Profil de la randonnée : ".html_entity_decode($_SESSION['nomRando']) );
imagettftext (  $image , 8 , 0 , 90 , 40 , $rouge , $dejaVuSans , "en rouge : profil des points de passage" );
imagettftext (  $image , 8 , 0 , 90 , 52 , $vert , $dejaVuSans , "en vert : profil des points de trace" );

imagepng($image);
?>

