<?php
include '../init.php';

$configPath = 'config/client.ini';
$config = parse_ini_file($configPath, true);

$trieur = new \Solire\Trieur\Trieur($config, 'dataTables');
$cf = $trieur->getDriver()->getJsConfig();

header('Content-type: application/json');
echo json_encode(array('config' => $cf));
