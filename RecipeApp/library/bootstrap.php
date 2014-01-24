<?php

function __include_file($class) {
	$filename = strtolower($class);

	if (file_exists(ROOT . DS . 'library' . DS . $filename . '.class.php')) {
		require_once ROOT . DS . 'library' . DS . $filename . '.class.php';
	} else if (file_exists(ROOT . DS . 'include' . DS . $filename . '.class.php')) {
		require ROOT . DS . 'include' . DS . $filename . '.class.php';
	} else {
		throw new FileNotFoundException("Could not load {$filename}.");
	}

	if (!class_exists($class)) throw new Exception("Class {$class} is undefined.");
}

function dispatch() {
	echo 'Called ' . __FUNCTION__ . '<br />';

	$uri = Request::server('REQUEST_URI');
	$request = new HttpRequest($uri);
	$dispatcher = new Dispatcher();
	$dispatcher->setController($request->getController());
	$dispatcer->setAction($request->getAction());
	$dispatcher->setArgs($request->getArgs());
	$dispatcher->dispatch();
}

function bootstrapInit() {
	echo 'Called ' . __FUNCTION__ . '<br />';
	require_once (ROOT . DS . 'library' . DS . 'config.php');
	spl_autoload_register('__include_file');
}

function bootstrapFull() {
	echo 'Called ' . __FUNCTION__ . '<br />';
	bootstrapInit();
	dispatch();
}
