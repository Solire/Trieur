<?php

namespace solire\trieur;

class Trieur
{
    /**
     * List of supported drivers and their mappings to the driver classes.
     *
     * To add your own driver use the 'driverClass' parameter to
     * {@link DriverManager::getConnection()}.
     *
     * @var array
     */
    private static $driverMap = array(
        'datatables' => 'solire\trieur\Driver\DataTables',
    );

    public function __construct()
    {}

    public static function getDriver($config, $driverName = null)
    {
        if (is_array($config)) {
            $config = new Config($config);
        }

        $driverName = strtolower($driverName);
        if (isset(self::$driverMap[$driverName])) {
            $driverClass = self::$driverMap[$driverName];
        } else {
            $driverClass = 'solire\trieur\Driver';
        }

        $driver = new $driverClass($config);

        return $driver;
    }
}
