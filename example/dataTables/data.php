<?php
include '../init.php';

use Solire\Conf\Conf;

class Data
{
    public static function run()
    {
        $configPath = 'config/client.json';
        $array = json_decode(file_get_contents($configPath), true);
        $conf = arrayToConf($array);

        $configDbPath = 'config/connection.ini';
        $configDb = parse_ini_file($configDbPath);
        $configDb['driverOptions'] = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        $doctrineConnection = \Doctrine\DBAL\DriverManager::getConnection($configDb);

        $trieur = new \Solire\Trieur\Trieur();
        $trieur
            ->init($conf, $doctrineConnection)
            ->run()
            ->setRequest($_POST)
        ;

        $response = $trieur->getResponse();

        return $response;
    }
}

try {
    $response = Data::run();
    header('Content-type: application/json');
    echo json_encode($response);
} catch (\Exception $ex) {
    header('Content-type: text/html; charset: utf-8');
    echo '<pre>' . print_r($ex, true) . '</pre>';
}
