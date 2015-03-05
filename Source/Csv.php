<?php
namespace Solire\Trieur\Source;

use Solire\Trieur\Source;
use Solire\Trieur\SourceSearch;
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
            throw new Exception('No csv file founed : "' . $connection . '"');
        }

        if (!is_readable($this->connection)) {
            throw new Exception('Csv file not readable : "' . realpath($connection) . '"');
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
        return fgetcsv(
            $this->handle,
            $this->conf->length,
            $this->conf->delimiter,
            $this->conf->enclosure
        );
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
        if (empty($this->orders)) {
            return false;
        }

        foreach ($this->orders as $order) {
            list($column, $dir) = $order;

            $test = strnatcasecmp(
                $row1[$column->sourceName],
                $row2[$column->sourceName]
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
            $this->searches,
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
        while ($this->row = $this->fetch()) {
            if ($this->search()) {
                $this->addToEligible($this->row);
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

    protected function processSearch(SourceSearch $filter)
    {
        $filter->setRow($this->row);
        return $filter->filter();
    }
}
