<?php
namespace Solire\Trieur\Connection;

use Solire\Trieur\Connection;
use Solire\Conf\Conf;
use Exception;

/**
 * Doctrine connection wrapper
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Csv extends Connection
{
    protected $count = 0;

    protected $filteredCount = 0;

    protected $data = [];

    protected $md5 = null;

    /**
     *
     * @param type $connection
     * @param Conf $conf
     * @param Conf $columns
     * @throws Exception
     */
    public function __construct($connection, Conf $conf, Conf $columns)
    {
        if (!file_exists($connection)) {
            throw new Exception('No csv file founed : "' . $connection . '"');
        }

        if (!is_readable($connection)) {
            throw new Exception('Csv file not readable : "' . realpath($connection) . '"');
        }

        if (!isset($conf->length)) {
            $conf->length = 0;
        }
        if (!isset($conf->delimiter)) {
            $conf->delimiter = ',';
        }
        if (!isset($conf->enclosure)) {
            $conf->enclosure = '"';
        }

        parent::__construct($connection, $conf, $columns);
    }

    public function getCount()
    {
        $this->read();

        return $this->count;
    }

    public function getFilteredCount()
    {
        $this->read();

        return $this->filteredCount;
    }

    public function getData()
    {
        $this->read();

        return $this->data;
    }

    protected function addToEligible($newRow)
    {
        foreach ($this->data as $offset => $row) {
            if ($this->inferieur($newRow, $row)) {
                return $this->insertToEligible($newRow, $offset);
            }
        }

        return $this->insertToEligible($newRow, count($this->data));
    }

    protected function insertToEligible($row, $offset)
    {
        $this->data = array_merge(
            array_slice($this->data, 0, $offset),
            [$row],
            array_slice($this->data, $offset)
        );
    }

    protected function inferieur($row1, $row2)
    {
        if (empty($this->order)) {
            return false;
        }

        foreach ($this->order as $order) {
            list($col, $dir) = $order;

            if ($row1[$col] === $row2[$col]) {
                continue;
            }

            $test = $row1[$col] < $row2[$col];

            if ($dir == 'ASC') {
                return $test;
            }

            return !$test;
        }
    }

    protected function read()
    {
        $currentMd5 = md5(serialize([
            $this->searches,
            $this->order,
            $this->offset,
            $this->length,
        ]));

        if ($this->md5 == $currentMd5) {
            return;
        }

        $this->md5 = $currentMd5;

        $this->data = [];
        $handle = fopen($this->connection, 'r');
        while ($row = fgetcsv(
                $handle,
                $this->conf->length,
                $this->conf->delimiter,
                $this->conf->enclosure
            )
        ) {
            if ($this->search($row)) {
                $this->addToEligible($row);
                $this->filteredCount++;
            }
            $this->count++;
        }

        $this->data = array_slice(
            $this->data,
            $this->offset,
            $this->length
        );
    }

    protected function search($row)
    {
        if (empty($this->searches)) {
            return true;
        }

        foreach ($this->searches as $searches) {
            foreach ($searches as $search) {
                $founed = $this->processSearch($row, $search);
            }

            if ($founed === false) {
                return false;
            }
        }

        return true;
    }

    protected function processSearch($row, $search)
    {
        $type = 'text';
        if (count($search) == 3) {
            list($columns, $terms, $type) = $search;
        } else {
            list($columns, $terms) = $search;
        }

        if ($type == 'text') {
            if (is_array($terms)) {
                $term = implode(' ', $terms);
            } else {
                $term = $terms;
            }

            $words = preg_split('`\s+`', $term);
            foreach ($words as $word) {
                foreach ($columns as $column) {
                    if (stripos($row[$column], $word) !== false) {
                        return true;
                    }
                }
            }
        } else {
            return true;
        }

        return false;
    }
}
