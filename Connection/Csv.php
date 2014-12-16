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
     * Constructeur
     *
     * @param string $connection Chemin du fichier csv
     * @param Conf   $conf       Configuration for the csv parse (length,
     * delimiter, enclosure)
     * @param Conf   $columns    Configuration des colonnes
     *
     * @throws Exception Si le fichier source n'existe pas ou n'est pas lisible
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

    /**
     * Return the total of available lines
     *
     * @return int Total number
     */
    public function getCount()
    {
        $this->parse();

        return $this->count;
    }

    /**
     * Return the total of available lines filtered by the current search
     *
     * @return int Total number
     */
    public function getFilteredCount()
    {
        $this->parse();

        return $this->filteredCount;
    }

    /**
     * Returns the data filtered by the current search
     *
     * @return mixed
     */
    public function getData()
    {
        $this->parse();

        return $this->data;
    }

    /**
     * Add a row to the data following the defined orders
     *
     * @param array $newRow The row
     *
     * @return void
     */
    protected function addToEligible($newRow)
    {
        $newOffset = count($this->data);
        foreach ($this->data as $offset => $row) {
            if ($this->lowerThan($newRow, $row)) {
                $newOffset = $offset;
                break;
            }
        }

        $this->insertToEligible($newRow, $newOffset);
    }

    /**
     * Inserts a row in the data at a defined offset
     *
     * @param array $row    The row
     * @param int   $offset The offset
     *
     * @return void
     */
    protected function insertToEligible($row, $offset)
    {
        $this->data = array_merge(
            array_slice($this->data, 0, $offset),
            [$row],
            array_slice($this->data, $offset)
        );
    }

    /**
     * Compare two rows of data
     *
     * @param type $row1 The first row
     * @param type $row2 The second row
     *
     * @return bool Returns true if $row1 is less than $row2,
     * false if $row1 is greater or equal than $row2, following the defined
     * orders
     */
    protected function lowerThan($row1, $row2)
    {
        if (empty($this->order)) {
            return false;
        }

        foreach ($this->order as $order) {
            list($col, $dir) = $order;

            $test = strnatcasecmp($row1[$col], $row2[$col]);

            if ($test == 0) {
                continue;
            }

            if (strtolower($dir) == 'asc') {
                return $test < 0;
            }

            return $test > 0;
        }

        return false;
    }

    /**
     * Parses the csv file, and build the data array
     *
     * @return void
     */
    protected function parse()
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

    /**
     * Checks if a row follows the defined searches
     *
     * @param type $row The row
     *
     * @return boolean
     */
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

    /**
     * Check if a row follows a search
     *
     * @param array $row    The row
     * @param array $search The search
     *
     * @return bool
     */
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
