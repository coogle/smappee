<?php
	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/vendor/coogle/smappee/src/SmappeeLocal.php';

	// Smappee username and password.
	define('SMAPPEE_HOST', '192.168.0.163');
	define('SMAPPEE_PASSWORD', 'admin');


	use Coogle\SmappeeLocal;

	$smappee = new SmappeeLocal(SMAPPEE_HOST, SMAPPEE_PASSWORD);

	// Logon
	$smappee->logon();
	
	// Get realtime usage
	$data = $smappee->getInstantaneous();
	var_dump($data);

	// List all ComfortPlugs
	$data = $smappee->listComfortPlugs();
	var_dump($data);

	// Turn ComfortPlug 1 on
	$smappee->setComfortPlug(1,1);
	
	// Turn ComfortPlug 1 off
	$smappee->setComfortPlug(1,0);
