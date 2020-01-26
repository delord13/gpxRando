<?php

////////////////////////////////////////////////////////////////////////////////
//                          profil_vitesse.php
//                          application gpx2tdm 
//
//    Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill
//    http://gpx2tdm.free.fr/CeCILL/
////////////////////////////////////////////////////////////////////////////////
//	création de l'image profil de vitesse
//
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
$dejaVuSansBold = $chemin . "DejaVuSans-Bold.ttf";
$dejaVuSansMonoBold = $chemin . "DejaVuSansMono-Bold.ttf";

$vitesseSession = $_SESSION['vitesse']; //$_SESSION['vitesse']
$kPos = $_SESSION['kPos'] ; //$_SESSION['kPos']
$kNeg = $_SESSION['kNeg'] ; //$_SESSION['kNeg']

// paramétrage du graphique
$margeHaut = 50;
$margeBas = 40;
$margeDroite = 30;
$margeGauche = 45;
$hauteurTotale = 440;
$largeurTotale = 645;
$hauteur = $hauteurTotale-$margeHaut-$margeBas; 
$largeur = $largeurTotale-$margeGauche-$margeDroite; 

// créer image et jeu de couleurscouleurs
// couleur de fond : jaune pâle (255, 255, 128)
// jeu de couleur ; le premier est la couleur de fond
$image = imagecreate($largeurTotale,$hauteurTotale);
$blanc =  imagecolorallocate($image, 255, 255, 255);
$jauneClair = imagecolorallocate($image, 255, 255, 128);
$gris0 =  imagecolorallocate($image, 153, 153, 153);
$gris1 =  imagecolorallocate($image, 102, 102, 102);
$gris2 =  imagecolorallocate($image, 76, 76, 76);
$gris3 =  imagecolorallocate($image, 60, 60, 60);
$gris4 =  imagecolorallocate($image, 42, 42, 42);
$noir = imagecolorallocate($image, 0, 0, 0);
$bleu = imagecolorallocate($image, 0, 168, 240);
$vert = imagecolorallocate($image, 192, 246, 0);

$vitesseMax = (int) round($vitesseSession+0.5,0); 
// taille en pixel de 1km/h
$tailleVitesse = $hauteur/$vitesseMax;
// taille en pixel de 10%
$tailleDix = $largeur/20;

// origine des axes
$x0 = $margeGauche;
$y0 = (int) round($margeHaut+$vitesseMax*$tailleVitesse,0);

// dessin des verticales (de -100% à +100%)
// gradué par 10%
for ($pourcent=0; $pourcent<=20;$pourcent++) {
	if ($pourcent==0 OR $pourcent==10 OR $pourcent==20) $couleur = $gris3; else $couleur = $gris0;
	imageline($image, $x0+(int) round($pourcent*$tailleDix,0), $y0, $x0+(int) round($pourcent*$tailleDix,0), $y0-$hauteur, $couleur);
	// étiquettes des %
	$chP =(string) (-100+$pourcent*10)."%";
	if (-100+$pourcent*10>0) $decalage = - (int) round(strlen($chP)/2*6,0);
	else $decalage = - (int) round(strlen($chP)/2*6,0);
	imagettftext (  $image , 8 , 0 , $x0+(int) round($pourcent*$tailleDix,0)+$decalage , $y0+17 , $gris3 , $dejaVuSansMono , $chP);
}

// dessin des horizontales (de 0 à vitesseMax)
// gradué par 10%
for ($v=0; $v<=$vitesseMax;$v++) {
	if ($v==0 OR $v==$vitesseMax) $couleur = $gris3; else $couleur = $gris0;
		imageline($image, $x0, (int) round($y0-$v*$tailleVitesse,0), $x0+$largeur, (int) round($y0-$v*$tailleVitesse,0), $couleur);
		// étiquettes des vitesses
//	$chP =(string) (-100+$pourcent*10)."%";
		if ($v<10) $decalage = -25; else $decalage = -32;
		$chV = $v.".0";
		imagettftext (  $image , 8 , 0 , $x0+$decalage , (int) round($y0-$v*$tailleVitesse,0)+4 , $gris3 , $dejaVuSansMono , $chV);
}

// tracé du profil par défaut
for ($p=-100; $p<100; $p=$p+2) {
	$v0 = (int) round($profilVitesse[$p]*$vitesseSession*$tailleVitesse ,0);
	$v1 = (int) round($profilVitesse[$p+2]*$vitesseSession*$tailleVitesse ,0);
	$yV0 = $y0 - $v0;
	$yV1 = $y0 - $v1;

	$p0 = (int) round(($p+100)/10*$tailleDix,0);
	$p1 = (int) round(($p+2+100)/10*$tailleDix,0);
	$xP0 = $x0 + $p0;
	$xP1 = $x0 + $p1;
	imageline($image, $xP0, $yV0, $xP1, $yV1, $vert);
	imageline($image, $xP0+1, $yV0, $xP1+1, $yV1, $vert);
	imageline($image, $xP0, $yV0+1, $xP1, $yV1+1, $vert);

}


// tracé du profil choisi
for ($p=-100; $p<100; $p=$p+2) {
	if ($p<0) $k = $kNeg; else $k = $kPos;
		$v0 = (int) round(pow($profilVitesse[$p],$k)*$vitesseSession*$tailleVitesse ,0);
		$v1 = (int) round(pow($profilVitesse[$p+2],$k)*$vitesseSession*$tailleVitesse ,0);
	$yV0 = $y0 - $v0;
	$yV1 = $y0 - $v1;

	$p0 = (int) round(($p+100)/10*$tailleDix,0);
	$p1 = (int) round(($p+2+100)/10*$tailleDix,0);
	$xP0 = $x0 + $p0;
	$xP1 = $x0 + $p1;
	imageline($image, $xP0, $yV0, $xP1, $yV1, $bleu);
	imageline($image, $xP0+1, $yV0, $xP1+1, $yV1, $bleu);
	imageline($image, $xP0, $yV0+1, $xP1, $yV1+1, $bleu);

}

// titre
imagettftext (  $image , 10 , 0 , (int) ($largeur+$margeGauche)/2 - 36 , 25 , $noir , $dejaVuSansBold , "Profil de vitesse");
imagettftext (  $image , 8 , 0 , (int) ($largeur+$margeGauche)/2 - 50 , 40 , $noir , $dejaVuSans , "en fonction de la pente en %");

// cartouche
imagefilledrectangle($image,$margeGauche+1,$margeHaut+1,$margeGauche+112,$margeHaut+39,$blanc);
//gris pour vert
imageline($image, $margeGauche+5,$margeHaut+5,$margeGauche+20,$margeHaut+5, $gris0);
imageline($image, $margeGauche+5,$margeHaut+17,$margeGauche+20,$margeHaut+17, $gris0);
imageline($image, $margeGauche+5,$margeHaut+5,$margeGauche+5,$margeHaut+17, $gris0);
imageline($image, $margeGauche+20,$margeHaut+5,$margeGauche+20,$margeHaut+17, $gris0);
// vert
imagefilledrectangle($image,$margeGauche+7,$margeHaut+7,$margeGauche+18,$margeHaut+15,$vert);
imagettftext (  $image , 8 , 0 , $margeGauche+25 , $margeHaut+15 , $gris1 , $dejaVuSans , "profil par défaut");

//gris pour bleu
imageline($image, $margeGauche+5,$margeHaut+22,$margeGauche+20,$margeHaut+22, $gris0);
imageline($image, $margeGauche+5,$margeHaut+34,$margeGauche+20,$margeHaut+34, $gris0);
imageline($image, $margeGauche+5,$margeHaut+22,$margeGauche+5,$margeHaut+34, $gris0);
imageline($image, $margeGauche+20,$margeHaut+22,$margeGauche+20,$margeHaut+34, $gris0);
// bleu
imagefilledrectangle($image,$margeGauche+7,$margeHaut+24,$margeGauche+18,$margeHaut+32,$bleu);
imagettftext (  $image , 8 , 0 , $margeGauche+25 , $margeHaut+32 , $gris1 , $dejaVuSans , "profil choisi");

imagepng($image);
?>
