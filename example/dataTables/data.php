<?php
include '../init.php';

use Solire\Conf\Conf;

class Data
{
    public static function run(){
        $configPath = 'config/client.json';
        $config = json_decode(file_get_contents($configPath));
        $conf = new Conf();
        foreach ($config as $key => $value) {
            $conf->set($value, $key);
        }

        $configDbPath = 'config/connection.ini';
        $configDb = parse_ini_file($configDbPath);
        $configDb['driverOptions'] = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        $doctrineConnection = \Doctrine\DBAL\DriverManager::getConnection($configDb);

        $trieur = new \Solire\Trieur\Trieur($conf, 'dataTables', $doctrineConnection);
        $trieur->getDriver()->setRequest($_POST);

        $data = $trieur->getConnection()->getDataQuery()->execute()->fetchAll(PDO::FETCH_ASSOC);
        $count = $trieur->getConnection()->getCountQuery()->execute()->fetch(PDO::FETCH_COLUMN);
        $fcount = $trieur->getConnection()->getFilteredCountQuery()->execute()->fetch(PDO::FETCH_COLUMN);

        $response = array(
            'data' => $data,
            'recordsTotal' => $count,
            'recordsFiltered' => $fcount,
            'request' => $_POST,
        );

        return $response;
    }
}

try {
    $response = Data::run();
    header('Content-type: application/json');
    echo json_encode($response);
} catch (Exception $ex) {
    header('Content-type: text/html; charset: utf-8');
    echo '<pre>' . print_r($ex, true) . '</pre>';
}
