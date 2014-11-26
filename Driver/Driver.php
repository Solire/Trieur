<?php

namespace Solire\Trieur\Driver;

use Solire\Conf\Conf;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Driver
{
    /**
     * List of columns with name index
     *
     * @var array
     */
    protected $columnsByName = [];

    /**
     * List of columns with numeric index
     *
     * @var array
     */
    protected $columnsByIndex = [];

    /**
     * The configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * The request
     *
     * @var array
     */
    protected $request;

    /**
     * Constructeur
     *
     * @param Conf $config  The driver configuration
     * @param Conf $columns The columns configuration
     */
    public function __construct(Conf $config, Conf $columns)
    {
        $this->config = $config;
        $this->setColumns($columns);
    }

    /**
     * Set the request
     *
     * @param mixed $request The request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Set the columns
     *
     * @return void
     */

    /**
     * Set the columns
     *
     * @param Conf $columns The columns list
     *
     * @return void
     */
    protected function setColumns(Conf $columns)
    {
        $index = 0;
        foreach ($columns as $name => $column) {
            $column->name = $name;
            $this->columnsByIndex[$index] = $column;
            $this->columnsByName[$name] = $column;

            $index++;
        }
    }
}
