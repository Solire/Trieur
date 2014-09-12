<?php

namespace Solire\Trieur;

use solire\trieur\Config;

interface Driver
{
    public function __construct(Config $config);

    public function setRequest(array $request);

    public function offset();

    public function length();

    public function order();

    public function getFilterTerm();

    public function getSearchableColumns();

    public function getFilterTermByColumns();
}
