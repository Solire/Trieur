<?php

namespace Solire\Trieur\Driver;

use \Solire\Trieur\Config;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Driver
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
     * @param Config $config The configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
