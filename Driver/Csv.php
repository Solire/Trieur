<?php
namespace Solire\Trieur\Driver;

use Solire\Trieur\Driver;
use Solire\Trieur\Columns;
use Solire\Conf\Conf;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Csv extends Driver
{
    /**
     * Constructeur
     *
     * @param Conf    $config  The configuration to build the csv (maximum length,
     * delimiter and the enclosure)
     * @param Columns $columns The columns configuration
     */
    public function __construct(Conf $config, Columns $columns)
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

    /**
     * Return the offset
     *
     * @return int
     */
    public function getOffset()
    {
        return null;
    }

    /**
     * Return the number of lines
     *
     * @return int
     */
    public function getLength()
    {
        return null;
    }

    /**
     * Return the order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return [];
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Return the content formated in csv
     *
     * @param array $data          The data filtered by the current search,
     * offset and length
     * @param int   $count         The total of available lines filtered by the
     * current search
     * @param int   $filteredCount The total of available lines
     *
     * @return array
     */
    public function getResponse(array $data, $count = null, $filteredCount = null)
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR
                  . uniqid('php-solire-trieur', true);
        $handle = fopen($filename, 'w');

        if (!$handle) {
            throw new Exception('Unable to create file "' . $filename . '"');
        }

        foreach ($data as $row) {
            fputcsv($handle, $row, $this->config->delimiter, $this->config->enclosure);
        }

        return file_get_contents($filename);
    }
}
