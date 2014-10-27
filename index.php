<?php

use nFramework\Application;
use nFramework\Request;
use nFramework\Response;

require_once 'nFramework' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = new Application();
$app->serve(
  new Request($_GET, $_POST, $_SERVER),
  new Response()
);