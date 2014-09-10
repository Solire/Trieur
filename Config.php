<?php

namespace Solire\Trieur;

class Config
{
    protected $config = array();

    protected $columns = array();

    /**
     *
     *
     * @param array $config
     */
    public function __construct($config)
    {
        foreach ($config as $key => $column) {
            if (substr($key, 0, 2) == '__') {
                $this->config[substr($key, 2)] = $column;
            } else {
                $column['name'] = $key;
                $this->columns[] = $column;
            }
        }
    }
}
