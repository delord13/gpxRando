<?php
////////////////////////////////////////////////////////////////////////////////
//                            fichRandoPdf.php
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
//	require_once('ficheRandoConfigAlt.php');
	require_once('../TCPDF/tcpdf.php');

// début html
$html = <<<EOT
		<style type="text/css">
			body {font-family: sans-serif; font-size: 12px; margin: 0px; padding: 0px;}
			p {margin-top: 0px; margin-bottom: 0px; text-align: justify;}
			.titre {margin-top: 5px; margin-bottom: 5px;font-size: 20px; font-weight: bold; text-align: center;}
			.sousTitre {font-size: 16px;  margin-bottom: 5px;font-weight: bold; text-align: center;}
			.presentation {font-size: 12px; margin-bottom: 5px; font-style: italic;}
			.remarques {font-size: 12px; margin-bottom: 0px; font-weight: bold;}
			.gras {font-weight: bold;}
			.complements {font-style: italic;}
		</style>
EOT;
			// traitement du logo
				if (!$_SESSION['ficheLogoAucun']) { 
					// si le fichier logo n'existe pas dans ../images (logo fourni pas utilisateur) alors le créer
					$cheminImages = realpath("../images");
					$cheminLogoTemp = $cheminImages."/logoTemp.".$_SESSION['ficheLogoImageType'];
					if (file_exists($cheminLogoTemp)) unlink($cheminLogoTemp) ;
					// chaîne contenu du fichier
					$contenu = base64_decode(str_replace( "\r\n", "", $_SESSION['ficheLogoImage']) );
					// création du fichier temporaire
					$handle = fopen($cheminLogoTemp,"w");
					fwrite($handle,$contenu);
					fclose($handle);
					// realpath
		
$html .= "
			<img src=\"$cheminLogoTemp\"/>
			
";

					
				}
			// traitement des titre sous-titre et date

$html .= " 			<p class=\"titre\">".$_SESSION['ficheTitreFiche']."</p>";

				if ($_SESSION['ficheSousTitre']!="") {
$html .= "
					<p class=\"sousTitre\">".$_SESSION['ficheSousTitre']."</p>
					";
				}
				$date = $_SESSION['date'];
				$tab_date = explode("/",$date);
				$jour = $tab_date[0];
				$mois = $tab_date[1];
				$an = $tab_date[2];
				setlocale(LC_TIME, 'fr_FR.UTF8');
				$laDate = ucfirst(strftime("%A %d %B %Y", mktime(0, 0, 0, $mois, $jour, $an)));
$html .= "
				<p class=\"sousTitre\">".$laDate."</p>
				";
				
				$presentation = "<p class=\"presentation\">".trim($_SESSION['fichePresentation'])."</p>";
				
				
				if ($presentation!=""){ 
$html .= $presentation;
				}	
				
				$remarques = str_replace("\n","<br>",trim($_SESSION['ficheRemarques']));
				if (trim($_SESSION['ficheRemarques']!="")){ 
$html .= "<p class=\"remarques\">".$remarques."</p>";
				}

			// traitement des rubriques
				//theme pour CPC
				if ($GLOBALS['clients']=="CPC") {
					if ($_SESSION['ficheThemeCode']!="") {
						// recherche intitulé du thème 
						$sql = "SELECT `intituleTheme` FROM `theme` WHERE `codeTheme`='{$_SESSION['ficheThemeCode']}' ";
						$res = mysqli_query($GLOBALS['lkId'],$sql);
						$unTheme = mysqli_fetch_assoc($res);
						$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['themeCode']['id']."</span> : ".$unTheme['intituleTheme'];
						if ($_SESSION['ficheThemeDescription']!="") {
							$html .= " : ".$_SESSION['ficheThemeDescription'];
						}
						$html .= "<br>";
					}
				}

				if ($_SESSION['ficheNiveau']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['niveau']['id']."</span> : ".$_SESSION['ficheNiveau']."<br>";

				if (isset($_SESSION['ficheIbpIndex'])) {
					if ($_SESSION['ficheIbpIndex']!="") {
	$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['ibpIndex']['id']."</span> : ".$_SESSION['ficheIbpIndex']."<br>";
						if ($GLOBALS['clients']=="PUBLIC") {
	$html .= "<br>Cotation \"Effort\" FFRandonnée selon indice IBP : 0-25 : Facile ; 26-50 : Assez facile ; 51-75 : Peu difficile ; 76-100 : Assez difficile ; >100 : Difficile<br>";
						}
					}
				}
				
				if ($_SESSION['ficheDenivPosFiche']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['denivPosFiche']['id']."</span> : +".$_SESSION['ficheDenivPosFiche']."m<br>";
				if ($_SESSION['ficheDenivNegFiche']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['denivNegFiche']['id']."</span> : -".$_SESSION['ficheDenivNegFiche']."m<br>";
				if ($_SESSION['ficheLongueurFiche']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['longueurFiche']['id']."</span> : ".$_SESSION['ficheLongueurFiche']."km<br>";

				if ($_SESSION['ficheDureeFiche']!="") { 
					$h = floor($_SESSION['ficheDureeFiche']/60);
					$m = $_SESSION['ficheDureeFiche']%60;
					if ($m<10) $m = "0" . $m;
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['dureeFiche']['id']."</span> : ".$h."h".$m."<br>";

				$difficultes = str_replace("\n","<br>",$_SESSION['ficheDifficultes']);}
				if ($difficultes!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['difficultes']['id']."</span> : ".$difficultes."<br>";
				if ($_SESSION['ficheCarte']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['carte']['id']."</span> : ".$_SESSION['ficheCarte']."<br>";
				$rdv = str_replace("\n","<br>",$_SESSION['ficheRDV']);
				if ($rdv!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['rdv']['id']."</span> : ".$rdv."<br>";
				if ($_SESSION['ficheDepart']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['depart']['id']."</span> : ".$_SESSION['ficheDepart']."<br>";
				if ($_SESSION['ficheParking']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['parking']['id']."</span> : ".$_SESSION['ficheParking']."<br>";

				if ($_SESSION['ficheTrajetKm']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['trajetKm']['id']."</span> : ".$_SESSION['ficheTrajetKm']."<br>";

		if ($GLOBALS['clients']!="CPC") {
				$trajet = str_replace("\n","<br>",$_SESSION['ficheTrajet']);
				if ($trajet!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['trajet']['id']."</span> : <br>".$trajet."<br>";
		}
				if ($_SESSION['ficheCovoiturage']!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['covoiturage']['id']."</span> : ".$_SESSION['ficheCovoiturage']."<br>";
				
				$laRandonnee = str_replace("\n","<br>",$_SESSION['ficheLaRandonnee']);
				if ($laRandonnee!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['laRandonnee']['id']."</span> : <br>".$laRandonnee."<br>";
				$equipement = str_replace("\n","<br>",$_SESSION['ficheEquipement']);
				if ($equipement!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['equipement']['id']."</span> : ".$equipement."<br>";
				
				$animateur = str_replace("\n","<br>",$_SESSION['ficheAnimateur']);
				
				if ($animateur!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['animateur']['id']."</span> : ".$animateur."<br>";
				
				$ficheComplementsTitre = $_SESSION['ficheComplementsTitre'];
				if ($ficheComplementsTitre!="") $ficheComplementsTitre .= " : "; 
				
				$ficheComplements = str_replace("\n","<br>",$_SESSION['ficheComplements']);
                
            if ($ficheComplements!="") { 
$html .= "<span class=\"complements\">".$ficheComplementsTitre."</span><br><span class=\"complements\">".$ficheComplements."</span><br>";
				}
		if ($GLOBALS['clients']=="CPC") {
				$trajet = str_replace("\n","<br>",$_SESSION['ficheTrajet']);
				if ($trajet!="") 
$html .= "<span class=\"gras\">".$GLOBALS['txt']['ficheRando']['trajet']['id']."</span> : <br>".$trajet."<br>";
		}

//$html .= "			</div>";

//echo($html); die();

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {


	// Page footer
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
		}
		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $_SESSION['nomRando']." ".$w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $_SESSION['nomRando']." ".$w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
	}

}

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('gpxRando');
	$pdf->SetTitle('Fiche rando');
	$pdf->SetSubject('Fiche, Rando');
	$pdf->SetKeywords('Fiche, Rando');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('gpxRando');
	$pdf->SetTitle('Fiche rando');
	$pdf->SetSubject('Fiche, Rando');
	$pdf->SetKeywords('Fiche, Rando');

	$pdf->setPrintHeader(false);

	$pdf->setFooterData(array(0,64,0), array(0,64,128));

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	global $l;
	$l = Array();

	// PAGE META DESCRIPTORS --------------------------------------

	$l['a_meta_charset'] = 'UTF-8';
	$l['a_meta_dir'] = 'ltr';
	$l['a_meta_language'] = 'fr';

	// TRANSLATIONS --------------------------------------
	$l['w_page'] = 'page';
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------
	// fin du remplacement

	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	$pdf->SetFont('dejavusans', '', 9, '', true); // 14

	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();

	// ---------------------------------------------------------

	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	$pdf->writeHTML($html, true, false, true, false, '');
	$pdf->Output($_SESSION['nomRando'].'.pdf', 'I');


