<?php
include '../init.php';

$configPath = 'config/client.ini';
$config = parse_ini_file($configPath, true);

$configDbPath = 'config/connection.ini';
$configDb = parse_ini_file($configDbPath);
$configDb['driverOptions'] = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);
$doctrineConnection = \Doctrine\DBAL\DriverManager::getConnection($configDb);

$trieur = new \Solire\Trieur\Trieur($config, 'dataTables', $doctrineConnection);
$trieur->getDriver()->setRequest($_POST);

$data = $trieur->getConnection()->getDataQuery()->execute()->fetchAll(PDO::FETCH_ASSOC);
$count = $trieur->getConnection()->getRawCountQuery()->execute()->fetch(PDO::FETCH_COLUMN);
$fcount = $trieur->getConnection()->getFilteredCountQuery()->execute()->fetch(PDO::FETCH_COLUMN);

$response = array(
    'data' => $data,
    'recordsTotal' => $count,
    'recordsFiltered' => $fcount,
    'request' => $_POST,
);

header('Content-type: application/json');
echo json_encode($response);
