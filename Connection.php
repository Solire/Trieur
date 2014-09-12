<?php

namespace Solire\Trieur;

interface Connection
{
    /**
     * Constructeur
     *
     * @param mixed                 $connection
     * @param \Solire\Trieur\Driver $driver
     * @param \Solire\Trieur\Config $config
     */
    public function __construct($connection, Driver $driver, Config $config);
}
