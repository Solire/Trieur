<?php
namespace Solire\Trieur\Source;

use Solire\Trieur\Source;
use Solire\Trieur\SourceFilter;
use Solire\Trieur\Columns;
use Solire\Trieur\Exception;
use Solire\Conf\Conf;

/**
 * Doctrine connection wrapper
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Csv extends Source
{
    /**
     * The csv file handle
     *
     * @var resource
     */
    protected $handle = null;

    /**
     * Number lines in the csv file
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Number lines matching the request in the csv file
     *
     * @var int
     */
    protected $filteredCount = 0;

    /**
     * Lines of the csv file matching the request
     *
     * @var array
     */
    protected $data = [];

    /**
     * Current row
     *
     * @var array
     */
    protected $row = null;

    /**
     * Header row
     *
     * @var array
     */
    protected $header = null;

    /**
     * State of the request (to ensure we don't parse the file multiple times)
     *
     * @var string
     */
    protected $md5 = null;

    /**
     * Constructeur
     *
     * @param string  $connection Chemin du fichier csv
     * @param Conf    $conf       Configuration for the csv parse (length,
     * delimiter, enclosure)
     * @param Columns $columns    Configuration des colonnes
     *
     * @throws Exception Si le fichier source n'existe pas ou n'est pas lisible
     */
    public function __construct(
        Conf $conf,
        Columns $columns,
        $connection
    ) {
        parent::__construct($conf, $columns, $connection);

        if (!file_exists($this->connection)) {
            throw new Exception('No csv file founed : "' . $this->connection . '"');
        }

        if (!is_readable($this->connection)) {
            throw new Exception('Csv file not readable : "' . realpath($this->connection) . '"');
        }

        if (!isset($this->conf->length)) {
            $this->conf->length = 0;
        }
        if (!isset($this->conf->delimiter)) {
            $this->conf->delimiter = ',';
        }
        if (!isset($this->conf->enclosure)) {
            $this->conf->enclosure = '"';
        }
    }

    /**
     * Opens the csv file
     *
     * @return void
     */
    protected function handle()
    {
        $this->handle = fopen($this->connection, 'r');
    }

    /**
     * Fetches a line from csv file
     *
     * @return array
     */
    protected function fetch()
    {
        $this->row = fgetcsv(
            $this->handle,
            $this->conf->length,
            $this->conf->delimiter,
            $this->conf->enclosure
        );

        return $this->row;
    }

    /**
     * Closes the handle to the csv file
     *
     * @return void
     */
    protected function close()
    {
        fclose($this->handle);
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
     * Return the total of available lines filtered by the current filters
     *
     * @return int Total number
     */
    public function getFilteredCount()
    {
        $this->parse();

        return $this->filteredCount;
    }

    /**
     * Returns the data filtered by the current filters
     *
     * @return mixed
     */
    public function getData()
    {
        $this->parse();

        if (!empty($this->header)) {
            $data = $this->data;
            $this->data = [];

            foreach ($data as $row) {
                $fRow = [];
                foreach ($row as $ind => $cell) {
                    $fRow[$this->header[$ind]] = $cell;
                }
                $this->data[] = $fRow;
            }
        }

        return $this->data;
    }

    /**
     * Add a row to the data following the defined orders
     *
     * @param array $newRow The row
     *
     * @return void
     */
    protected function addToEligible($newRow = null)
    {
        if ($newRow === null) {
            $newRow = $this->row;
        }

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
        if (empty($this->orders)) {
            return false;
        }

        foreach ($this->orders as $order) {
            list($column, $dir) = $order;

            $test = strnatcasecmp(
                self::getCell($row1, $column->sourceName),
                self::getCell($row2, $column->sourceName)
            );

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
            $this->filters,
            $this->orders,
            $this->offset,
            $this->length,
        ]));

        if ($this->md5 == $currentMd5) {
            return;
        }

        $this->md5 = $currentMd5;
        $this->count = 0;
        $this->filteredCount = 0;
        $this->handle();

        $this->data = [];

        $count = 0;
        if (isset($this->conf->header)) {
            while ($this->fetch()) {
                if ($this->conf->header == $count) {
                    $this->header = $this->row;
                    break;
                }

                $count++;
            }
        }

        while ($this->fetch()) {
//            if (!empty($this->header)) {
//                $row = $this->row;
//                $this->row = [];
//                foreach ($row as $ind => $cell) {
//                    $this->row[$this->header[$ind]] = $cell;
//                }
//                unset($row);
//            }

            if ($this->filter()) {
                $this->addToEligible();
                $this->filteredCount++;
            }
            $this->count++;
        }

        $this->close();

        $this->data = array_slice(
            $this->data,
            $this->offset,
            $this->length
        );
    }

    protected function processFilter(SourceFilter $filter)
    {
        $filter->setRow($this->row);
        $filter->setSource($this);
        return $filter->filter();
    }

    public function getCell($row, $index)
    {
        if ($this->header && !is_numeric($index)) {
            $index = array_search($index, $this->header);
        }

        return $row[$index];
    }
}
