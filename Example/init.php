<?php
/**
 * Affichage des erreurs
 */
error_reporting(E_ALL);
ini_set('display_errors', 'On');

set_error_handler(function($errno, $errstr, $errfile, $errline){
    throw new Exception();
});

require __DIR__ . '/autoload.php';

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$run = new Run;

$handler = new PrettyPageHandler;
$run->pushHandler($handler);

$jsonHandler = new JsonResponseHandler;
$jsonHandler->onlyForAjaxRequests(true);
$run->pushHandler($jsonHandler);

$run->register();
