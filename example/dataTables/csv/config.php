<?php
include '../../init.php';

use Symfony\Component\Yaml\Yaml;
use Solire\Trieur\Trieur;

class Config
{
    public static $jsConfig = null;
    public static $jsYadcfConfig = null;

    public static function run()
    {
        $configPath = 'config/client.yml';
        $array = Yaml::parse($configPath);
        $conf = arrayToConf($array);

        $trieur = new Trieur($conf);

        self::$jsConfig = $trieur->getDriver()->getJsConfig();
        self::$jsYadcfConfig = $trieur->getDriver()->getYadcfConfig();
    }
}

$jsConfig = Config::run();
header('Content-type: application/json');
echo json_encode([
    'config' => Config::$jsConfig,
    'yadcfConfig' => Config::$jsYadcfConfig,
]);
