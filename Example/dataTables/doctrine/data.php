<?php

include '../../init.php';

use Doctrine\DBAL\DriverManager;
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

        $configDbPath = 'config/connection.ini';
        $configDb = parse_ini_file($configDbPath);
        $configDb['driverOptions'] = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ];
        $doctrineConnection = DriverManager::getConnection($configDb);

        $trieur = new Trieur($conf, $doctrineConnection);
        $trieur->setRequest($_POST);

        $response = $trieur->getResponse();

        $response['debug'] = $trieur->getSource()->getDataQuery()->getSQL();

        return $response;
    }
}

$response = Data::run();
header('Content-type: application/json');
echo json_encode($response);
