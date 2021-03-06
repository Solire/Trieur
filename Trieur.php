<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Trieur.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Trieur extends \Pimple\Container
{
    /**
     * Columns list.
     *
     * @var Columns
     */
    protected $columns = null;

    /**
     * Configuration.
     *
     * @var Conf
     */
    protected $conf = null;

    /**
     * Driver.
     *
     * @var Driver
     */
    protected $driver = null;

    /**
     * Source to the database.
     *
     * @var Source
     */
    protected $source = null;

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
     * List of supported source class and their mappings to the source
     * wrapper classes.
     *
     * @var type
     */
    private static $sourceMap = [
        'doctrine' => '\Solire\Trieur\Source\Doctrine',
        'csv' => '\Solire\Trieur\Source\Csv',
    ];

    /**
     * Constructor.
     *
     * @param Conf  $conf        The configuration
     * @param mixed $sourceModel The database source object
     */
    public function __construct(Conf $conf, $sourceModel = null)
    {
        $this->init($conf, $sourceModel);
        $this->run();
    }

    /**
     * Initialise the container, and prepare the instanciation of the driver
     * and source class.
     *
     * @param Conf  $conf        The configuration
     * @param mixed $sourceModel The data source object
     *
     * @return void
     */
    private function init(Conf $conf, $sourceModel = null)
    {
        $this->conf = $conf;
        $this->initColumns();
        $this->initDriver();

        if ($sourceModel !== null) {
            $this['sourceModel'] = $sourceModel;
            $this->initSource();
        }

        $this->initFormat();
    }

    /**
     * Instanciate the driver and source class.
     *
     * @return void
     */
    private function run()
    {
        $this->setColumns($this['columns']);
        $this->setDriver($this['driver']);
        if (isset($this['source'])) {
            $this->setSource($this['source']);
        }
    }

    /**
     * Build Colmumns configuration object.
     *
     * @return void
     */
    protected function initColumns()
    {
        if (!isset($this->conf->columns)) {
            $this->conf->columns = new Conf();
        }

        $this['columns'] = function ($c) {
            return new Columns(
                $c->conf->columns
            );
        };
    }

    /**
     * Find the driver class.
     *
     * @return void
     */
    protected function findDriverClass()
    {
        if (isset($this->conf->driver->class)) {
            if (!class_exists($this->conf->driver->class)) {
                throw new Exception(
                    sprintf(
                        'class "%s" does not exist',
                        $this->conf->driver->class
                    )
                );
            }

            if (!is_subclass_of($this->conf->driver->class, '\Solire\Trieur\Driver')) {
                throw new Exception(
                    sprintf(
                        'class "%s" does not extend abstract class '
                        . '"\Solire\Trieur\Driver"',
                        $this->conf->driver->class
                    )
                );
            }
        } elseif (isset($this->conf->driver->name)
            && isset(self::$driverMap[$this->conf->driver->name])
        ) {
            $this->conf->driver->class = self::$driverMap[
                $this->conf->driver->name
            ];
        } else {
            throw new Exception(
                'No class for driver class founed or given'
            );
        }
    }

    /**
     * Build Driver object.
     *
     * @return void
     */
    protected function initDriver()
    {
        $this->findDriverClass();

        if (!isset($this->conf->driver->conf)) {
            $this->conf->driver->conf = new Conf();
        }

        $this['driver'] = function ($c) {
            $className = $c->conf->driver->class;

            return new $className(
                $c->conf->driver->conf,
                $this['columns']
            );
        };
    }

    /**
     * Find the source class.
     *
     * @return void
     *
     * @throws \Exception If no wrapper class found
     */
    protected function findSourceClass()
    {
        if (isset($this->conf->source->class)) {
            if (!class_exists($this->conf->source->class)) {
                throw new Exception(
                    sprintf(
                        'class "%s" does not exist',
                        $this->conf->source->class
                    )
                );
            }

            if (!is_subclass_of($this->conf->source->class, '\Solire\Trieur\Source')) {
                throw new Exception(
                    sprintf(
                        'class "%s" does not extend '
                        . 'abstract class "\Solire\Trieur\Source"',
                        $this->conf->source->class
                    )
                );
            }

            return;
        }

        if (isset($this->conf->source->name)
            && isset(self::$sourceMap[$this->conf->source->name])
        ) {
            $this->conf->source->class = self::$sourceMap[
                $this->conf->source->name
            ];

            return;
        }

        throw new Exception(
            'No wrapper class for source class founed'
        );
    }

    /**
     * Build the source wrapper class.
     *
     * @return void
     */
    protected function initSource()
    {
        $this->findSourceClass();

        if (!isset($this->conf->source->conf)) {
            $this->conf->source->conf = new Conf();
        }

        $this['source'] = function ($c) {
            $className = $c->conf->source->class;

            return new $className(
                $c->conf->source->conf,
                $this['columns'],
                $c['sourceModel']
            );
        };
    }

    /**
     * Build the format class.
     *
     * @return void
     */
    protected function initFormat()
    {
        $this['format'] = function ($c) {
            return new Format(
                $this['columns']
            );
        };
    }

    /**
     * Sets the columns configuration.
     *
     * @param Columns $columns The columns configuration
     *
     * @return self
     */
    public function setColumns(Columns $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets the driver.
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
     * Sets the source wrapper.
     *
     * @param Source $source The data source
     *
     * @return self
     */
    public function setSource(Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the Driver.
     *
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the source wrapper object.
     *
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the request.
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
     * Prepare the fetch.
     *
     * @return self
     */
    public function prepare()
    {
        $filters = $this->driver->getFilters();
        if (!empty($filters)) {
            $this->source->addFilters($filters);
        }

        $this->source->setLength($this->driver->getLength());
        $this->source->setOffset($this->driver->getOffset());
        $this->source->setOrders($this->driver->getOrder());

        return $this;
    }

    /**
     * Prepare the fetch.
     *
     * @return self
     */
    public function fetch()
    {
        return $this->driver->getResponse(
            $this['format']->format($this->source->getData()),
            $this->source->getCount(),
            $this->source->getFilteredCount()
        );
    }

    /**
     * Returns the response.
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->prepare()->fetch();
    }
}
