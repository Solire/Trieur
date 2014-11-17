<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

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
     * @var Conf
     */
    protected $conf = null;

    /**
     *
     * @var
     */
    protected $columns = null;

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
     * @param Conf   $conf       The Configuration
     * @param string $driverName The driver name
     * @param mixed  $connection The database connection
     */
    public function __construct($conf, $driverName = null, $connection = null)
    {
        $this->buildConf($conf);
        $this->buildColumns();
        $this->buildDriver($driverName);
        $this->buildConnection($connection);
    }

    /**
     * Build and affect the Configuration object
     *
     * @param Conf $conf The Configuration
     *
     * @return void
     */
    protected function buildConf($conf)
    {
        $this->conf = $conf;

    }

    protected function buildColumns()
    {
        $this->columns = array_merge(
            (array) $this->conf->columns,
            array_values((array) $this->conf->columns)
        );
    }

    /**
     * Build Driver object
     *
     * @param string $driverName The driver's name
     *
     * @return void
     */
    protected function buildDriver($driverName = null)
    {
        if ($driverName !== null && !isset(self::$driverMap[$driverName])) {
            throw new \Exception(
                'No wrapper class defined for : {' . $driverName . '}'
            );
        }

        $driverClass = '\Solire\Trieur\Driver\Driver';
        if ($driverName !== null) {
            $driverClass = self::$driverMap[$driverName];
        }

        $this->driver = new $driverClass(
            $this->conf->driver,
            $this->columns
        );
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
            $this->conf->connection
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
