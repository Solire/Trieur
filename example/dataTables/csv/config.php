<?php
include '../../init.php';

use \Symfony\Component\Yaml\Yaml;

class Config
{
    public static function run()
    {
        $configPath = 'config/client.json';
        $array = Yaml::parse($configPath);
        $conf = arrayToConf($array);

        $trieur = new \Solire\Trieur\Trieur($conf);

        return $trieur->getDriver()->getJsConfig();
    }
}

$jsConfig = Config::run();
header('Content-type: application/json');
echo json_encode([
    'config' => $jsConfig,
]);
