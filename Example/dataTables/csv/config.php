<?php

include '../../init.php';

use Solire\Conf\Loader;
use Solire\Trieur\Trieur;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public static $jsConfig = null;
    public static $jsColumnFilterConfig = null;

    public static function run()
    {
        $configPath = 'config/client.yml';
        $array = Yaml::parse($configPath);
        $conf = Loader::load($array);

        $trieur = new Trieur($conf);

        self::$jsConfig = $trieur->getDriver()->getJsConfig();
        self::$jsColumnFilterConfig = $trieur->getDriver()->getColumnFilterConfig();
    }
}

$jsConfig = Config::run();
header('Content-type: application/json');
echo json_encode([
    'config' => Config::$jsConfig,
    'columnFilterConfig' => Config::$jsColumnFilterConfig,
]);
