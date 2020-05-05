<?php
	////////////////////////////////////////////////////////////////////////////////////////////////
	// sessionsMultiplesSousAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////
	// require '../../inc/sessionsMultiplesSousAppli.inc.php'; //  se trouve dans applis/inc ; à placer au début des scripts secondaires de chaque appli qui se trouvent dans un sous-répertoire de l'appli'
	// pour éviter les mélanges de session
	// NB : sessionsMultiplesAppli.inc.php doit avoir été inclus dans le script principal de l'appli
	// l'index de l'appli doit comporter :
		// session_start();
		// output_add_rewrite_var('SESSION_NAME',session_name());

	// contrôle de l'accès par index.php
//	if (!isset($_REQUEST['SESSION_NAME'])) die('Cette page ne peut pas être appelée directement.');
	
	session_name($_REQUEST['SESSION_NAME']);
	session_start();
	// réecriture des url <a> et <form>
	// nb il faudra compléter les url dans header et dans javascript
	// exemples :
		// header(location:'sousAppli.php?SESSION_NAME='.session_name());
		// onClick="javascript:window.open('sousAppli.php?SESSION_NAME=<<point d'interrogation'>php echo(session_name());<point d'interrogation'>>','_blank')"
	output_add_rewrite_var('SESSION_NAME',$_REQUEST['SESSION_NAME']);
	// fin sessionsMultiplesSousAppli.inc.php
	////////////////////////////////////////////////////////////////////////////////////////////////
?>