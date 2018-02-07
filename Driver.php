<?php

namespace Solire\Trieur;

use Solire\Conf\Conf;

/**
 * Datatables driver.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Driver
{
    /**
     * The configuration.
     *
     * @var Config
     */
    protected $config;

    /**
     * The columns configuration.
     *
     * @var Columns
     */
    protected $columns;

    /**
     * The request.
     *
     * @var array
     */
    protected $request;

    /**
     * Constructeur.
     *
     * @param Conf $config  The driver configuration
     * @param Conf $columns The columns configuration
     */
    public function __construct(Conf $config, Columns $columns)
    {
        $this->config = $config;
        $this->columns = $columns;
    }

    /**
     * Set the request.
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
     * Return the offset.
     *
     * @return int
     */
    abstract public function getOffset();

    /**
     * Return the number of lines.
     *
     * @return int
     */
    abstract public function getLength();

    /**
     * Return the order.
     *
     * @return mixed
     */
    abstract public function getOrder();

    /**
     * Return the filters.
     *
     * @return mixed
     */
    abstract public function getFilters();

    /**
     * Returns the response.
     *
     * @param array $data          The data filtered by the current filters,
     *                             offset and length, sorted by the current orders
     * @param int   $count         The total of available lines filtered by the
     *                             current filters
     * @param int   $filteredCount The total of available lines
     *
     * @return array
     */
    abstract public function getResponse(array $data, $count = null, $filteredCount = null);
}
