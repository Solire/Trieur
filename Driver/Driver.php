<?php

namespace Solire\Trieur\Driver;

use \Solire\Trieur\Config;

class Driver
{
    /**
     *
     * @var Config
     */
    protected $config;

    /**
     *
     * @var array
     */
    protected $request;

    /**
     *
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     *
     *
     * @param array $request
     *
     * @return void
     */
    public function setRequest(array $request)
    {
        $this->request = $request;
    }
}
