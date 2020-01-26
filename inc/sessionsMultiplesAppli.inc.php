<?php
/*
	////////////////////////////////////////////////////////////////////////////////////////////////
	// sessionsMultiplesAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////
	// require '../inc/sessionsMultiplesAppli.inc.php' ; // à placer au début du script principal de chaque appli
	// pour éviter les mélanges de session
	// NB : sessionsMultiplesSousAppli.inc.php doit avoir été inclus dans chaque script secondaire de l'appli
	session_start();
	
	// si un nom de session a été transmis
	// si le nom de session reçue n'est pas un nom de session de l'appli
	if(!preg_match('#^SESS[0-9]+$#',$_REQUEST['SESSION_NAME'])) {
		// récupération des données de session
		$sessionIdRecue = session_id();
		$sessionNameRecu = session_name();
		$sessionRecu = $_SESSION;
		// construction du nom de la session de l'appli 
		$_REQUEST['SESSION_NAME']='SESS'.uniqid('');
		// réecriture des url <a> et <form>
		// nb il faudra compléter les url dans header et dans javascript
		// exemples :
			// header(location:'sousAppli.php?SESSION_NAME='.session_name());
			// onClick="javascript:window.open('sousAppli.php?SESSION_NAME=<<point d'interrogation'>php echo(session_name());<point d'interrogation'>>','_blank')"
//		output_add_rewrite_var('SESSION_NAME',$_REQUEST['SESSION_NAME']);
		session_name($_REQUEST['SESSION_NAME']);
		output_add_rewrite_var('SESSION_NAME',$_REQUEST['SESSION_NAME']);
		// ouverture de la nouvelle session
		session_write_close();
		session_start();
		session_regenerate_id();
		// mémorisation des données externes intéressantes
		$sessionConnexion = $_SESSION['connexion'];
		// vidage des données de session
		unset($_SESSION);
		// passage en session des données externes intéressantes
		$_SESSION['connexion'] = $sessionConnexion;
	}
	else {
		// on continue avec la session de l'appli
		session_name($_REQUEST['SESSION_NAME']);
		session_start();
	}
	// fin sessionsMultiplesAppli.inc.php
	// fin sessionsMultiplesAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////

*/	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// sessionsMultiplesAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////
	// include(../inc/sessionsMultiplesAppli.inc.php); // se trouve dans applis/inc ; à placer au début du script principal de chaque appli
	// pour éviter les mélanges de session
	// les sessions seront alors indépendantes seul $_SESSION['connexion'] est conservé
	// l'index de l'appli doit comporter :
		// session_start();
		// output_add_rewrite_var('SESSION_NAME',session_name());

	// contrôle de l'accès par index.php
	if (!isset($_REQUEST['SESSION_NAME'])) die('Cette page ne peut pas être appelée directement.');
	// si le nom de session reçue n'est pas un nom de session de l'appli
	if(!preg_match('#^SESS[0-9]+$#',$_REQUEST['SESSION_NAME'])) {
		// ouverture session de l'appelant 
		session_name($_REQUEST['SESSION_NAME']);
		session_start();
		// récupération des données de session
		if (isset($_SESSION['connexion'])) $sessionConnexionRecue = $_SESSION['connexion'];
		// fermeture de la session de l'appelant
		session_write_close();

		// ouverture de la nouvelle sessionuniqid('')
		$newSessionName = 'SESS'.time();
		// réecriture des url <a> et <form>
		// nb il faudra compléter les url dans header et dans javascript
		// exemples :
			// header(location:'sousAppli.php?SESSION_NAME='.session_name());
			// onClick="javascript:window.open('sousAppli.php?SESSION_NAME=<<point d'interrogation'>php echo(session_name());<point d'interrogation'>>','_blank')"
		output_add_rewrite_var('SESSION_NAME',$newSessionName);
		session_name($newSessionName);
		session_start();
		session_regenerate_id();
		// vidage des données de session
		foreach ($_SESSION AS $key => $value) {
			unset($_SESSION[$key]);
		}
		// passage en session des données externes intéressantes
		if (isset($sessionConnexionRecue)) $_SESSION['connexion'] = $sessionConnexionRecue;
	}
	else {
		// on continue avec la session de l'appli
		output_add_rewrite_var('SESSION_NAME',$_REQUEST['SESSION_NAME']);
		session_name($_REQUEST['SESSION_NAME']);
		session_start();
	}
	// fin sessionsMultiplesAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////
