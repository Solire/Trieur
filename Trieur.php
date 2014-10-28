<?php

namespace Solire\Trieur;

/**
 * Trieur
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Trieur
{
    /**
     * Configuration
     *
     * @var Config
     */
    protected $config = null;

    /**
     * Driver
     *
     * @var Driver
     */
    protected $driver = null;

    /**
     * Connection to the database
     *
     * @var Connection
     */
    protected $connection = null;

    /**
     * List of supported drivers and their mappings to the driver classes.
     *
     * @var array
     */
    private static $driverMap = array(
        'dataTables' => '\Solire\Trieur\Driver\DataTables',
    );

    /**
     * List of supported connection class and their mappings to the connection
     * wrapper classes.
     *
     * @var type
     */
    private static $connectionMap = array(
        'Doctrine\DBAL\Connection' => '\Solire\Trieur\Connection\Doctrine',
    );

    /**
     * Constructor
     *
     * @param array|Config $config     The Configuration
     * @param string       $driverName The driver name
     * @param mixed        $connection The database connection
     */
    public function __construct($config, $driverName = null, $connection = null)
    {
        $this->buildConfig($config);
        $this->buildDriver($driverName);
        $this->buildConnection($connection);
    }

    /**
     * Build and affect the Config object
     *
     * @param array|Config $config The configuration
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function buildConfig($config)
    {
        if (is_array($config)) {
            $this->config = new Config($config);
        } elseif (is_object($config) && $config instanceof Config) {
            $this->config = $config;
        } else {
            throw new \InvalidArgumentException(
                'Wrong argument given for $config, should be '
                . '\Solire\Trieur\Config or array'
            );
        }
    }

    /**
     * Build Driver object
     *
     * @param string $driverName The driver's name
     *
     * @return void
     */
    public function buildDriver($driverName = null)
    {
        if ($driverName !== null && !isset(self::$driverMap[$driverName])) {
            throw new \Exception(
                'No wrapper class defined for : {' . $driverName . '}'
            );
        }

        $driverClass = '\Solire\Trieur\Driver\Driver';
        if ($driverName !== null) {
            $this->config->setDriverName($driverName);
            $driverClass = self::$driverMap[$driverName];
        }

        $this->driver = new $driverClass($this->config);
    }

    /**
     * Build the connection wrapper classe
     *
     * @param mixed $connection The database connection object
     *
     * @return void
     */
    public function buildConnection($connection = null)
    {
        if ($connection === null) {
            return;
        }

        $className = get_class($connection);
        if (!isset(self::$connectionMap[$className])) {
            throw new \Exception(
                'No wrapper class for connection class : {' . $className . '}'
            );
        }

        $connectionWrapperClass = self::$connectionMap[$className];
        $this->connection = new $connectionWrapperClass(
            $connection,
            $this->driver,
            $this->config
        );
    }

    /**
     * Get the Driver
     *
     * @return Driver\Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the connection wrapper object
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
