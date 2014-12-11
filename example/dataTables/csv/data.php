<?php
include '../../init.php';

use \Symfony\Component\Yaml\Yaml;

class Data
{
    public static function run()
    {
        $configPath = 'config/client.yml';
        $array = Yaml::parse($configPath);
        $conf = arrayToConf($array);

        $configDbPath = 'config/connection.ini';
        $configDb = parse_ini_file($configDbPath);
        $configDb['driverOptions'] = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ];
        $doctrineConnection = \Doctrine\DBAL\DriverManager::getConnection($configDb);

        $trieur = new \Solire\Trieur\Trieur($conf, $doctrineConnection);
        $trieur->setRequest($_POST);

        $response = $trieur->getResponse();

        return $response;
    }
}

$response = Data::run();
header('Content-type: application/json');
echo json_encode($response);
