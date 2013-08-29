<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', getcwd());

require_once (ROOT . DS . 'library' . DS . 'bootstrap.php');

bootstrapInit();

$database = MySqlDatabase::instance(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
$database->connect();

$recipe = $database->query('recipes', array('name'))
	->resultClass('Recipe')
	->fetch();

echo "{$recipe->name}<br />";

$result = $database->query('recipes')
	->resultClass('Recipe');

while ($recipe = $result->fetch()) {
	echo "{$recipe->name}<br />";
}

$database->query('recipes')
	->save(array(
			'name' => 'Test Recipe 3',
		));

$database->query('recipes')
	->where('name', 'Test Recipe 3')
	->save(array(
			'name' => 'Test Recipe 4',
		));

$result = $database->query('recipes', array('id'))
	->where('name', 'Test Recipe 3')
	->resultBoth()
	->fetch();

$recipe_id = $result['id'];

$database->query('ingredients')
	->save(array(
			'name' => 'Eggs',
			'quantity' => 2,
			'recipe_id' => $recipe_id
		));

$database->query('recipes')
	->where('name', 'Test Recipe 3')
	->delete();