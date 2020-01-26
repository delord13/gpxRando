   
		// début function lancerOpenRoute		
		var originalCursors = new Array(2);
		var rep = [];
    
		function cursorModifyEntirePage(CursorType){
			var elements = document.body.getElementsByTagName('*');
			let lclCntr = 0;
			originalCursors.length = elements.length; 
			for(lclCntr = 0; lclCntr < elements.length; lclCntr++){
				originalCursors[lclCntr] = elements[lclCntr].style.cursor;
				elements[lclCntr].style.cursor = CursorType;
			}
		}

		function cursorRestoreEntirePage(){
			let lclCntr = 0;
			var elements = document.body.getElementsByTagName('*');
			for(lclCntr = 0; lclCntr < elements.length; lclCntr++){
				elements[lclCntr].style.cursor = originalCursors[lclCntr];
			}
		}
		
		function lancerOpenRoute() {
			cursorModifyEntirePage('wait');
			// appel de openrouteservice.php
			var request = new XMLHttpRequest();
			var coord = document.getElementById("ficheItiDepartLon").value+","+document.getElementById("ficheItiDepartLat").value+"%7C"+document.getElementById("ficheItiArriveeLon").value+","+document.getElementById("ficheItiArriveeLat").value;
			var url = 'util/openrouteservice.php?coord='+coord;

			request.open('GET', url, true);

			request.onreadystatechange = function () {
				if (this.readyState === 4) {
					json = JSON.stringify(eval('(' + this.responseText + ')')); //convert to json string
					rep = JSON.parse(json); //convert to javascript array
					if (rep['sansPeage']['itineraire']!=undefined) { // pas d'erreur'

						// construction du ou des itinéraires fournis par openRoute Service
						// SANS péage
						var itineraire = "Calculé par OpenRoute Service : itinéraire sans péage Aller-Retour  "+2*rep['sansPeage']['km']+" km - temps de parcours Aller "+rep['sansPeage']['tempsAffiche']+"\n"+rep['sansPeage']['itineraire'];
						document.getElementById('ficheItiModeVisualiserSans').value = rep['sansPeage']['modeVisualiser'];
						document.getElementById('ficheItiTitrePageSans').value = 'sansPeage';
						
						document.getElementById('ficheItiXmlSans').value = rep['sansPeage']['gpxTrk'].replace(new RegExp("\"", 'g'),"'");
						document.getElementById('visuSans').style.display = "inline";
						
						// ficheTrajetKm
						var ficheTrajetKm = document.getElementById('ficheTrajetKm');
						if (ficheTrajetKm.value=="") ficheTrajetKm.value = rep['sansPeage']['km']*2;
						
						// covoiturage seulement pour IRLPT
						
						// AVEC péage
						if (rep['avecPeage']!="") {
							itineraire += "\n\n"+"Calculé par OpenRoute Service : itinéraire alternatif avec péage Aller-Retour "+2*rep['avecPeage']['km']+" km - temps de parcours Aller "+rep['avecPeage']['tempsAffiche']+"\n"+rep['avecPeage']['itineraire'];
							document.getElementById('ficheItiModeVisualiserAvec').value = rep['avecPeage']['modeVisualiser'];
							document.getElementById('ficheItiTitrePageAvec').value = 'avecPeage';
							document.getElementById('ficheItiXmlAvec').value =  rep['avecPeage']['gpxTrk'].replace(new RegExp("\"", 'g'),"'");;
							document.getElementById('visuAvec').style.display = "inline";
						}
					}
					else { // erreur détectée
						itineraire = "OpenRoute Service n'a pas pu calculer d'itinéraire."
					}
					// affectation de ficheTrajet
					trajet = document.getElementById('ficheTrajet');
					ancienContenu = trajet.value;
					if (ancienContenu!="") {
						nouveauContenu = ancienContenu+"\n\n"+itineraire;
					}
					else {
						nouveauContenu = itineraire;
					}
					trajet.value = nouveauContenu;
				}
					cursorRestoreEntirePage();
			};
			request.send();
		} 
		// fin function lancerOpenRoute
		
		function visualiserCarte(type) {
			if (type=="sansPeage") {
				document.getElementById('modeVisualiserIti').value = document.getElementById('ficheItiTitrePageSans').value;
				document.getElementById('titrePageIti').value = document.getElementById('ficheItiTitrePageSans').value;
				document.getElementById('xmlIti').value = document.getElementById('ficheItiXmlSans').value;
			} 
			else {
				document.getElementById('modeVisualiserIti').value = document.getElementById('ficheItiModeVisualiserAvec').value;
				
				document.getElementById('titrePageIti').value = document.getElementById('ficheItiTitrePageAvec').value;
				
				document.getElementById('xmlIti').value = document.getElementById('ficheItiXmlAvec').value;
			}
			document.getElementById('formVisualiserIti').submit();
		}
		

			function confirmerNouveau() {
				Check = confirm("Attention ! \n\nSi vous n\'avez pas enregistré le tableau courant, il sera perdu.\n\nVoulez vous vraiment créer ou charger un nouveau tableau de marche ?");
				if(Check == true) {
					formulaire = document.getElementById('formRetour');
					formulaire.submit();
//					history.back();
				};
			}

			function confirmerSansDate() {
				if (document.getElementById("inputDate").value=="") {
					Check = confirm("Attention ! \n\nCe tableau de marche n\'a pas de date.\n\nVoulez vous vraiment l\'enregistrer sans date ?");
					if(Check == true) {
						document.getElementById("formMenu").newAction.value='enregistrer'; 
						document.getElementById("formMenu").target='_self'; 
						document.getElementById("formMenu").submit();
					}
					else {
						document.getElementById("inputDate").focus();
						document.getElementById("inputDate").click();
						return false;
					}
				}
				else {
					document.getElementById("formMenu").newAction.value='enregistrer'; 
					document.getElementById("formMenu").target='_self'; 
					document.getElementById("formMenu").submit();
				}
				
			}
			
			function afficher(id) {
				document.getElementById("tdm").style.display = "none";
				document.getElementById("profil").style.display = "none";
				document.getElementById("ficheRando").style.display = "none";
				document.getElementById("aide").style.display = "none";
				document.getElementById('heureMinute').style.display='none';
				if (id!="tdm") {
					document.getElementById('profilVitesse').style.display='none';
					updateProfilVitesse('off');
				}
				
				document.getElementById("onglettdm").className = "onglet";
				document.getElementById("ongletprofil").className = "onglet";
				document.getElementById("ongletficheRando").className = "onglet";
				document.getElementById("ongletaide").className = "onglet";

				document.getElementById("onglet"+id).className = "ongletActif";

				document.getElementById(id).style.display = "block";

				document.getElementsByName("idAffiche")[0].value = id;
				document.getElementsByName("idAffiche")[1].value = id;
				document.getElementsByName("idAffiche")[2].value = id;
			}

			function updateProfilVitesse(onOff) {
				document.getElementsByName("profilVitesse")[0].value = onOff;
				document.getElementsByName("profilVitesse")[1].value = onOff;
				document.getElementsByName("profilVitesse")[2].value = onOff;
				document.getElementsByName("profilVitesse")[3].value = onOff;
			}

			function alerte(){
				alert('L\'impression d\'un tableau à partir du navigateur internet ne donne pas toujours de très bons résultats, c\'est pouquoi vous pouvez souhaiter \"imprimer\" la page dans un fichier pdf.\n\n Voici la procédure à suivre avec Mozilla Firefox :\n- Cliquez sur \"Fichier/Aperçu avant impression\" de votre navigateur\n- Cliquez sur Mise en page, choisisez Paysage\n- Cliquez sur Imprimer\n- Choisissez Imprimer dans  un fichier format pdf\n- Donnez un nom au fichier et choisissez un dossier de destination\n- Cliquez sur Imprimer\n- Cliquez sur Fermer\n- Fermez la fenêtre\n\n Voici la procédure à suivre avec Internet Explorer :\n- Vous devez avoir installé l\'extension PDFCreator\n- Cliquez sur \"OK\"\n- Répondez \"Non\" à la question : \"Voulez-vous fermer cette fenêtre ?\"\n- Cliquez sur PDFCreator\n- Enregistrez le fichichier pdf\n- Fermez la fenêtre ');
			}

			function initialiserGraphique() {
				document.getElementById('profilVitesse').style.display='inline'; // inline
			}

			function getPosition(element) {
				var left = 0;
				var top = 0;
				/*On récupère l'élément*/
				var e = document.getElementById(element);
				/*Tant que l'on a un élément parent*/
				while (e.offsetParent != undefined && e.offsetParent != null)
				{
				/*On ajoute la position de l'élément parent*/
				left += e.offsetLeft + (e.clientLeft != null ? e.clientLeft : 0);
				top += e.offsetTop + (e.clientTop != null ? e.clientTop : 0);
				e = e.offsetParent;
				}
				var res = new Array;
				res['left'] = left;
				res['top'] = top;
				return res;
			}

			function getSize(element) {
				var width = 0;
				var height = 0;
				var e = document.getElementById(element);
				width = e.offsetWidth;
				height = e.offsetHeight;
				var res = new Array;
				res['width'] = width;
				res['height'] = height;
				return res;
			}


			function changerHeure(n) {
				for (var i=0; i<24; i++) {
					document.getElementById("tdH"+i.toString()).className = "tdNonSelect";
				}
				document.getElementById("tdH"+n.toString()).className = "tdSelect";
				var ha0 = document.getElementById("heureArrivee").value;
				var h0 = ha0.slice(0,2);
				var m0 = ha0.slice(3,5);
				if (n<10) h1 = "0"+n.toString();
				else h1 = n.toString();
				document.getElementById("heureArrivee").value = h1+":"+m0;
				document.getElementById('heureMinute').style.display='none';
			}

			function changerMinute(n) {
				for (var i=0; i<12; i++) {
					document.getElementById("tdM"+i.toString()).className = "tdNonSelect";
				}
				document.getElementById("tdM"+n.toString()).className = "tdSelect";
				var ha0 = document.getElementById("heureArrivee").value;
				var h0 = ha0.slice(0,2);
				var m0 = ha0.slice(3,5);
				var nn = n*5;
				if (nn<10) m1 = "0"+nn.toString();
				else m1 = nn.toString();
				document.getElementById("heureArrivee").value = h0+":"+m1;
				document.getElementById('heureMinute').style.display='none';
			}

			function ouvrirHeureMinute() {
				var ha0 = document.getElementById("heureArrivee").value;
				var h0 = 0;
				if (ha0.slice(0,1)=="0") h0 = parseInt(ha0.slice(1,2));
				else h0 = parseInt(ha0.slice(0,2));
				var m0 = 0;
				if (ha0.slice(3,4)=="0") m0 = parseInt(ha0.slice(4,5))/5;
				else m0 = parseInt(ha0.slice(3,5))/5;
				document.getElementById("tdH"+h0.toString()).className = "tdSelect";
				document.getElementById("tdM"+m0.toString()).className = "tdSelect";

				var left = getPosition('heureArrivee')['left'] + getSize('heureArrivee')['width'];
				var top = getPosition('heureArrivee')['top'] ;

				document.getElementById('heureMinute').style.left = left+"px";
				document.getElementById('heureMinute').style.top = top+"px";

				document.getElementById('heureMinute').style.display = 'block';
			}
			
			function reactiverSessionSiNecessaire(lastUpdateSession) {
				var formMenu = document.getElementById('formMenu');
				var now = new Date();
				var maintenant = now.getTime();
				// si plus de 9 minutes écoulées
				if ((maintenant-lastUpdateSession)>540000) { // 9 minutes : 540000
					formMenu.newAction.value='recalculer'; formMenu.target='_self'; formMenu.submit();
				}
			}

			function initialiserGraphiqueOngletEtReactiverSession(idAffiche, lastUpdateSession) {
			// constuire le graphique du profil de vitesse
//				initialiserGraphique();
				// afficher le dernier ongler ouvert ou TDM
				afficher(idAffiche);
				// lancer l'action recalculer pour réactivité les sessions de plus de 10 minutes
				setInterval("reactiverSessionSiNecessaire("+lastUpdateSession+")", 60000); // toutes les minutes : 60000
			}

