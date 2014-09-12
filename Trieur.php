<?php

namespace Solire\Trieur;

class Trieur
{
    /**
     *
     *
     * @var Config
     */
    protected $config = null;

    /**
     *
     * @var Driver
     */
    protected $driver = null;

    /**
     *
     * @var Connection
     */
    protected $connection = null;

    /**
     * List of supported drivers and their mappings to the driver classes.
     *
     * To add your own driver use the 'driverClass' parameter to
     * {@link DriverManager::getConnection()}.
     *
     * @var array
     */
    private static $driverMap = array(
        'dataTables' => '\Solire\Trieur\Driver\DataTables',
    );

    private static $connectionMap = array(
        'Doctrine\DBAL\Connection' => '\Solire\Trieur\Connection\Doctrine',
    );

    public function __construct($config, $driverName = null, $connection = null)
    {
        $this->buildConfig($config);
        $this->buildDriver($driverName);
        $this->buildConnection($connection);
    }

    public function buildConfig($config)
    {
        if (is_array($config)) {
            $this->config = new Config($config);
        } else if (is_object ($config) && $config instanceof Config) {
            $this->config = $config;
        } else {
            throw new \InvalidArgumentException(
                  'Wrong argument given for $config, should be '
                . '\Solire\Trieur\Config or array'
            );
        }
    }

    /**
     *
     *
     * @param string $driverName
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
     *
     *
     * @param mixed $connection
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

    public function getDriver()
    {
        return $this->driver;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
