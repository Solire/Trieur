<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace solire\trieur;

use solire\trieur\Config;

/**
 * Description of Driver
 *
 * @author thansen
 */
class Driver
{
    /**
     *
     *
     * @param Config $config
     * @param array  $request
     */
    public function __construct(Config $config, $request = null)
    {}

    public function offset()
    {}

    public function length()
    {}

    public function order()
    {}

    public function getFilterTerm()
    {}

    public function getFilterTermByColumns()
    {}
}
