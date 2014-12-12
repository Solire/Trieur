<?php
namespace Solire\Trieur\Connection;

use Solire\Trieur\Connection;
use Solire\Conf\Conf;

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

    public function __construct($connection, Conf $conf)
    {
        if (!isset($conf->length)) {
            $conf->length = 0;
        }
        if (!isset($conf->delimiter)) {
            $conf->delimiter = ',';
        }
        if (!isset($conf->enclosure)) {
            $conf->enclosure = '"';
        }

        parent::__construct($connection, $conf);

        $this->read();
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getFilteredCount()
    {
        return $this->filteredCount;
    }

    public function getData()
    {
        return $this->data;
    }

    protected function read()
    {
        $handle = fopen($this->connection, 'r');
        while ($row = fgetcsv($handle, $this->conf->length, $this->conf->delimiter, $this->conf->enclosure)) {
            if ($this->count >= $this->offset || $this->offset === null
                && $this->count < $this->offset + $this->length || $this->length === null
            ) {
                $this->data[] = $row;
            }
            $this->count++;
        }
    }

    protected function search($row)
    {
        if (empty($this->searches)) {
            return true;
        }

        foreach ($this->searches as $searches) {
            foreach ($searches as $search) {
                $founed = false;

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
                            if (stripos($row[$column], $word)) {
                                $founed = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($founed === false) {
                return false;
            }
        }

        return true;
    }
}
