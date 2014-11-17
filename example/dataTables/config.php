<?php
include '../init.php';

use Solire\Conf\Conf;

class Config
{
    public static function run()
    {
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
        return $trieur->getDriver()->getJsConfig();
    }
}

//$trieur = new \Solire\Trieur\Trieur($config, 'dataTables');
//$cf = $trieur->getDriver()->getJsConfig();

header('Content-type: application/json');
echo json_encode(array(
    'config' => Config::run(),
));
