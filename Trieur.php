<?php
namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Trieur
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Trieur extends \Pimple\Container
{
    /**
     * Columns list
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Configuration
     *
     * @var Conf
     */
    protected $conf = null;

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
    private static $driverMap = [
        'dataTables' => '\Solire\Trieur\Driver\DataTables',
        'csv' => '\Solire\Trieur\Driver\Csv',
    ];

    /**
     * List of supported connection class and their mappings to the connection
     * wrapper classes.
     *
     * @var type
     */
    private static $connectionMap = [
        'doctrine' => '\Solire\Trieur\Connection\Doctrine',
        'csv' => '\Solire\Trieur\Connection\Csv',
    ];

    /**
     * Constructor
     *
     * @param Conf  $conf            The configuration
     * @param mixed $connectionModel The database connection object
     */
    public function __construct(Conf $conf, $connectionModel = null)
    {
        $this->init($conf, $connectionModel);
        $this->run();
    }

    /**
     * Initialise the container, and prepare the instanciation of the driver
     * and connection class
     *
     * @param Conf  $conf            The configuration
     * @param mixed $connectionModel The database connection object
     *
     * @return void
     */
    private function init(Conf $conf, $connectionModel = null)
    {
        $this->conf = $conf;
        $this->initDriver();

        if ($connectionModel !== null) {
            $this['connectionModel'] = $connectionModel;
            $this->initConnection();
        }
    }

    /**
     * Instanciate the driver and connection class
     *
     * @return void
     */
    private function run()
    {
        $this->setDriver($this['driver']);
        if (isset($this['connection'])) {
            $this->setConnection($this['connection']);
        }
    }

    /**
     * Find the driver class
     *
     * @return void
     */
    protected function findDriverClass()
    {
        if (isset($this->conf->driver->class)) {
            $this->conf->driver->class = $this->conf->driver->class;
        } elseif (isset($this->conf->driver->name)
            && isset(self::$driverMap[$this->conf->driver->name])
        ) {
            $this->conf->driver->class = self::$driverMap[
                $this->conf->driver->name
            ];
        } else {
            throw new \Exception(
                'No class for driver class founed or given'
            );
        }
    }

    /**
     * Build Driver object
     *
     * @return void
     */
    protected function initDriver()
    {
        $this->findDriverClass();
        $this['driver'] = function ($c) {
            $className = $c->conf->driver->class;
            return new $className(
                $c->conf->driver->conf,
                $c->conf->columns
            );
        };
    }

    /**
     * Find the connection class
     *
     * @return void
     * @throws \Exception If no wrapper class found
     */
    protected function findConnectionClass()
    {
        if (isset($this->conf->connection->class)) {
            $this->conf->connection->class = $this->conf->driver->class;
        } elseif (isset($this->conf->connection->name)
            && isset(self::$connectionMap[$this->conf->connection->name])
        ) {
            $this->conf->connection->class = self::$connectionMap[
                $this->conf->connection->name
            ];
        } else {
            throw new \Exception(
                'No wrapper class for connection class founed'
            );
        }
    }

    /**
     * Build the connection wrapper class
     *
     * @return void
     */
    protected function initConnection()
    {
        $this->findConnectionClass();
        $this['connection'] = function ($c) {
            $className = $c->conf->connection->class;
            return new $className(
                $c['connectionModel'],
                $c->conf->connection->conf
            );
        };
    }

    /**
     * Sets the driver
     *
     * @param Driver $driver The driver
     *
     * @return self
     */
    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Sets the connection wrapper
     *
     * @param Connection $connection The data connection
     *
     * @return self
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get the Driver
     *
     * @return Driver
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

    /**
     * Set the request
     *
     * @param mixed $request The request
     *
     * @return self
     */
    public function setRequest($request)
    {
        $this->driver->setRequest($request);

        return $this;
    }

    /**
     * Returns the response
     *
     * @return array
     */
    public function getResponse()
    {
        $searches = $this->driver->getFilterTermByColumns();
        if (!empty($searches)) {
            $this->connection->addSearches($searches);
        }

        $term = $this->driver->getFilterTerm();
        if ($term) {
            $columns = $this->driver->getColumns(true, true);
            $this->connection->addSearch([
                [$columns, $term]
            ]);
        }

        $this->connection->setLength($this->driver->length());
        $this->connection->setOffset($this->driver->offset());

        $this->connection->setOrder($this->driver->order());

        return $this->driver->getResponse(
            $this->connection->getData(),
            $this->connection->getCount(),
            $this->connection->getFilteredCount()
        );
    }
}
