<?php
include '../../init.php';

use Symfony\Component\Yaml\Yaml;
use Solire\Trieur\Trieur;

class Config
{
    public static function run()
    {
        $configPath = 'config/client.yml';
        $array = Yaml::parse($configPath);
        $conf = arrayToConf($array);

        $trieur = new Trieur($conf);

        return $trieur->getDriver()->getJsConfig();
    }
}

$jsConfig = Config::run();
header('Content-type: application/json');
echo json_encode([
    'config' => $jsConfig,
]);
