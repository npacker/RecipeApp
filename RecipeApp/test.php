<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', getcwd());

require_once (ROOT . DS . 'library' . DS . 'bootstrap.php');

bootstrapInit();

$database = MySqlDatabase::instance(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
$database->connect();
$recipe = $database->query()
	->from('recipes', array('name'))
	->fetchClass('Recipe');
echo $recipe->name;
$result = $database->query()
	->from('recipes')
	->fetchBoth();
print_r($result);
$database->query()
	->from('recipes')
	->save(array('name' => 'Test Recipe 2'));
$result = $database->query()
	->from('recipes')
	->fetchBoth();
print_r($result);