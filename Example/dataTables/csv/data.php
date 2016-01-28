<?php

include '../../init.php';

use Solire\Conf\Loader;
use Solire\Trieur\Trieur;
use Symfony\Component\Yaml\Yaml;

class Data
{
    public static function run()
    {
        $configPath = 'config/client.yml';
        $array = Yaml::parse($configPath);
        $conf = Loader::load($array);

        $trieur = new Trieur($conf, 'data/clients.csv');
        $trieur->setRequest($_POST);

        $response = $trieur->getResponse();

        return $response;
    }
}

$response = Data::run();
//print_r($response);
header('Content-type: application/json');
echo json_encode($response);
