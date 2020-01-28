//--------------------------------------------------------------------------------
//	$Id: mouseposition_utm.js,v 1.8 2012/03/06 14:23:38 wolf Exp $
//--------------------------------------------------------------------------------
//	Erklaerung:	http://www.netzwolf.info/kartografie/openlayers/utm
//--------------------------------------------------------------------------------
//	Fragen, Wuensche, Bedenken, Anregungen?
//	<openlayers(%40)netzwolf.info>
//--------------------------------------------------------------------------------

OpenLayers.Control.MousePositionUTM = OpenLayers.Class (OpenLayers.Control.MousePosition, {

	utmr: false,

	formatOutput: function (lonLat) {

		if (lonLat.lat > 84.0) return '[Lattitude > 84&#176;N]';
		if (lonLat.lat <-80.0) return '[Lattitude < 80&#176;S]';
		lonLat.lon -= Math.floor(lonLat.lon/360+0.5)*360;

		//---------------------------------------------------------
		//	Konversion
		//---------------------------------------------------------

		var utm = this.geoToUtm (lonLat);

		//---------------------------------------------------------
		//	UTM?
		//---------------------------------------------------------

		if (!this.utmr) return 'UTM ' + utm.zone + utm.band +
			' '+ (utm.x +   500000)+
			' '+ (utm.y + 50000000).toString().substr(1);

		//---------------------------------------------------------
		//	UTMR
		//---------------------------------------------------------

		var tileX = Math.floor (utm.x /  100000.0) + 4;
		var nameX = this.squareNamesX.charAt (utm.zone%3*8 + tileX);

		var tileY = Math.floor (utm.y /  100000.0) + 100;
		var nameY = this.squareNamesY.charAt ((tileY + (utm.zone%2 ? 0 : 5))%20);

		var x     = (Math.floor (utm.x + 1000000.0) % 100000 + 100000).toString().substr(1);
		var y     = (Math.floor (utm.y +10000000.0) % 100000 + 100000).toString().substr(1);

		return 'UTMR ' + utm.zone + utm.band + ' ' + nameX + nameY + ' ' + x + ' ' + y;
	},

	bandNames: 'CDEFGHJKLMNPQRSTUVWXX',
	squareNamesX: 'STUVWXYZABCDEFGHJKLMNPQR',
	squareNamesY: 'ABCDEFGHJKLMNPQRSTUV',

	geoToUtm: function (lonLat) {

		//---------------------------------------------------------
		//	Bestimme Mittelmerdian
		//---------------------------------------------------------

		var zone = Math.floor (lonLat.lon/6.0) + 31;
		var meridian = zone*6 - 183;

		//---------------------------------------------------------
		//	Laenge relativ zum Mittelmeridian
		//	Uebergang auf Bogenmass
		//---------------------------------------------------------

		var rho = 180.0/3.14159265358979;
		var phi = lonLat.lat/rho;
		var lbd = (lonLat.lon-meridian)/rho;

		//---------------------------------------------------------
		//	Hilfswerte
		//	(Alle Konstanten fuer das WGS84-Ellipsoid)
		//---------------------------------------------------------

		var sin = Math.sin (phi);
		var cos = Math.cos (phi);
		var tan = Math.tan (phi);
		var c2  = cos*cos;
		var t2  = tan*tan;
		var l2  = lbd*lbd;
		var eta = 0.00673949674227643 * c2;
		var Vq  = 1 + eta;
		var V   = Math.sqrt(Vq);
		var N   = 6399593.62575849/V;

		//---------------------------------------------------------
		//	Konforme Transformation in Nord- und Ost-Wert
		//	.oO( you are not expected to understand this :-)
		//---------------------------------------------------------

		var nv = 6367449.14582342 * phi + sin * cos *
				(((0.00403587903241357 * c2 - 0.709736085843171)
				* c2 + 135.398511127892) * c2 - 32144.4799350778)
				+ N * (0.5 * tan * c2 * l2 * (1 + 1/12 * (5+9*eta-t2)*c2*l2));;
		var ev = N * cos * lbd * (1+ 1/6 * c2 * l2 * (Vq - t2 +0.05 * (5-18*t2+t2*t2) * c2 * l2));

		//---------------------------------------------------------
		//	Anzeige
		//---------------------------------------------------------
		return {
			zone: zone,
			band: this.bandNames.charAt(Math.floor(lonLat.lat/8.0+10.0)),
			x: Math.round (0.9996*ev),
			y: Math.round (0.9996*nv)
		}
	},

	CLASS_NAME:'OpenLayers.Control.MousePositionUTM'

});

//--------------------------------------------------------------------------------
//	$Id: mouseposition_utm.js,v 1.8 2012/03/06 14:23:38 wolf Exp $
//--------------------------------------------------------------------------------
