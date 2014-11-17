<?php

namespace Solire\Trieur\Driver;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class Driver
{
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
     * @param \Solire\Conf\Conf $config  The driver configuration
     * @param \Solire\Conf\Conf $columns The columns configuration
     */
    public function __construct($config, $columns)
    {
        $this->config = $config;
        $this->columns = $columns;
    }

    /**
     * Set the request
     *
     * @param array $request The request
     *
     * @return void
     */
    public function setRequest(array $request)
    {
        $this->request = $request;
    }
}
