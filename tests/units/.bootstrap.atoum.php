<?php

/*
This file will automatically be included before EACH test if -bf/--bootstrap-file argument is not used.

Use it to initialize the tested code, add autoloader, require mandatory file, or anything that needs to be done before EACH test.

More information on documentation:
[en] http://docs.atoum.org/en/chapter3.html#Bootstrap-file
[fr] http://docs.atoum.org/fr/chapter3.html#Fichier-de-bootstrap
*/

require __DIR__ . '/../../vendor/autoload.php';

// Génération d'un dossier temporaires pour les tests
define('TEST_TMP_DIR', __DIR__ . '/tmp');
if (!is_dir(TEST_TMP_DIR)) {
    mkdir(TEST_TMP_DIR);
}

/**
 * Converts an array to a Conf object
 *
 * @param array $array
 *
 * @return \Solire\Conf\Conf
 * @todo the Conf library will evolve
 */
function arrayToConf(array $array)
{
    $conf = new Solire\Conf\Conf();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $conf->set(arrayToConf($value), $key);
        } else {
            $conf->set($value, $key);
        }
    }
    return $conf;
}

