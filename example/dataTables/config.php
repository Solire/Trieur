<?php
include '../init.php';

use Solire\Conf\Conf;

class Config
{
    public static function run()
    {
        $configPath = 'config/client.json';
        $array = json_decode(file_get_contents($configPath), true);
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
