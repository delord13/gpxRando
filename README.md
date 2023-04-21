# gpxRando
cartographie et tableau de marche pour la randonnée pédestre

Copyright Michel Delord 12/04/2012 logiciel libre sous licence Cecill

http://gpx2tdm.free.fr/CeCILL/

Contributeurs : Michel Delord, Joël Dufour, Jean-Paul Fontaine, Jean Gullo, Alfeo Lotto, Jean-Claude Marie
 

L'application permet, à partir d'un fichier gpx contenant une trace et des waypoints, de construire:
* un tableau de marche
* un profil altimétrique
* une fiche descriptive de la randonnée
* une carte imprimable au format A4 sur fond IGN ou OSM Topo

L'application est écrite en PHP.

Elle utilise les bibliothèques :
* Leaflet avec les plugins leaflet-kml et Leaflet.MetricGrid
* l'extension Géoportail pour Leaflet
* jQuery
et les API :
* OpenRoute Service : pour le calcul des itinéraires routiers (en option) : https://openrouteservice.org/ 
* IBP Index pour le calcul de l'indice IBP : https://www.ibpindex.com/index.php/fr/ibp-services-fr/services
	
Le paramétrage de l'application se fait en modifiant le fichier inc/config.inc.php_MODELE que l'on doit renommer en config.inc.php après modification.

NB : 
* l'affichage des cartes topographiques IGN nécessite une clé Géoportail que l'on peut obtenir en créant un compte à l'adresse : http://professionnels.ign.fr/
* le calcul de l'indice IBP nécessite un clé que l'on peut obtenir à l'adresse : https://openrouteservice.org/
* le calcul de l'itinéraire routier pour atteindre le point de départ de la randonnée (en option) nécessite 2 clés que l'on peut obtenir à l'adresse : https://openrouteservice.org/
	
