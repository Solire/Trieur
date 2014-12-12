<?php
namespace Solire\Trieur\Driver;

use Solire\Trieur\Driver;
use Solire\Conf\Conf;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Csv extends Driver
{
    public function __construct(Conf $config, Conf $columns)
    {
        if (!isset($config->length)) {
            $config->length = 0;
        }
        if (!isset($config->delimiter)) {
            $config->delimiter = ',';
        }
        if (!isset($config->enclosure)) {
            $config->enclosure = '"';
        }

        parent::__construct($config, $columns);
    }

    public function getColumns($searchable = false, $connection = false)
    {
        ;
    }

    public function length()
    {
        return null;
    }

    public function offset()
    {
        return null;
    }

    public function order()
    {
        return [];
    }

    public function getFilterTerm()
    {
        return '';
    }

    public function getFilterTermByColumns()
    {
        return [];
    }

    public function getResponse(array $data, $count, $filteredCount)
    {
        $filename = 'tmp/clients.csv';
        $handle = fopen($filename, 'w');

        foreach ($data as $row) {
            fputcsv($handle, $row, $this->config->delimiter, $this->config->enclosure);
        }

        echo file_get_contents($filename);
    }
}
