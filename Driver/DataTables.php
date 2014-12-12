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
class DataTables extends Driver
{
    /**
     * Return the filter term
     *
     * @return array
     */
    public function getFilterTerm()
    {
        return $this->request['search']['value'];
    }



    /**
     * Determines if a column is searchable and has a search, if so return the
     * corresponding term
     *
     * @param Conf $column The column's configuration
     *
     * @return type
     */
    protected function getColumnTerm($column)
    {
        return $column['search']['value'];
    }

    /**
     * Check if a column is searchable, if it is then returns the column
     * expression
     *
     * @param array $column The column configuration
     *
     * @return string
     */
    protected function getColumnFilterConnection($column)
    {
        if ($column['searchable']
            && $column['filter']
        ) {
            if (isset($column['filterConnection'])) {
                return $column['filterConnection'];
            }

            if (isset($column['connection'])) {
                return $column['connection'];
            }
        }

        return null;
    }

    /**
     * Get the column list (all or only the column that can be searched)
     *
     * @param bool   $searchable True to return only the searchable columns
     * @param string $connection If false returns for each column the entire
     * configuration, if true returns only the connection parameter for the
     * search
     *
     * @return array
     */
    public function getColumns($searchable = false, $connection = false)
    {
        $columns = [];

        $clientColumns = null;
        if (isset($this->request['columns'])) {
            $clientColumns = $this->request['columns'];
        }
        $serverColumns = $this->columnsByIndex;

        foreach ($serverColumns as $index => $serverColumn) {
            if ($clientColumns !== null) {
                $column = array_merge(
                    (array) $serverColumn,
                    $clientColumns[$index]
                );
            } else {
                $column = $serverColumn;
            }

            $filterConnection = $this->getColumnFilterConnection($column);
            if (!$searchable || $filterConnection !== null) {
                if ($connection) {
                    $columns[] = $filterConnection;
                } else {
                    $columns[] = $column;
                }
            }
        }

        return $columns;
    }

    /**
     * Return the filter terms for each columns
     *
     * @return array
     */
    public function getFilterTermByColumns()
    {
        $filteredColumns = [];

        $columns = $this->getColumns(true);

        foreach ($columns as $index => $column) {
            $term = $this->getColumnTerm($column);
            if ($term !== '') {
                $connection = $this->getColumnFilterConnection($column);
                if (!is_array($connection)) {
                    $connection = [$connection];
                }

                if (isset($column['filterType'])
                    && $column['filterType'] == 'date-range'
                ) {
                    $terms = explode('~', $term);

                    $col = [];

                    if (!empty($terms[0])) {
                        /*
                         * @todo translate from date format sent to connection
                         */
                        $col[0] = $terms[0];
                    } else {
                        $col[0] = '';
                    }

                    if (!empty($terms[1])) {
                        /*
                         * @todo translate from date format sent to connection
                         */
                        $col[1] = $terms[1];
                    } else {
                        $col[1] = '';
                    }

                    $filteredColumns[] = [
                        [$connection, $col, 'date-range']
                    ];
                } else {
                    $filteredColumns[] = [
                        [$connection, $term, 'text']
                    ];
                }
            }
        }

        return $filteredColumns;
    }

    /**
     * Return the number of lines
     *
     * @return int
     */
    public function length()
    {
        return $this->request['length'];
    }

    /**
     * Return the offset
     *
     * @return int
     */
    public function offset()
    {
        return $this->request['start'];
    }

    /**
     * Return the list of columns for the sort with the direction
     *
     * @return array
     */
    public function order()
    {
        $orders = [];
        $ordersClient = $this->request['order'];
        foreach ($ordersClient as $order) {
            $columnName = $this->columnsByIndex[$order['column']]->connection;
            $dir        = $order['dir'];

            $orders[] = [
                $columnName,
                $dir,
            ];
        }
        return $orders;
    }

    protected function formateData(array $data)
    {
        $formatedData = [];

        foreach ($data as $row) {
            $formatedRow = [];
            foreach ($this->columnsByIndex as $index => $column) {
                if (isset($row[$column->name])) {
                    $formatedRow[$column->name] = $row[$column->name];
                } else {
                    $formatedRow[$column->name] = $row[$index];
                }
            }
            $formatedData[] = $formatedRow;
        }

        return $formatedData;
    }

    /**
     * Returns the response
     *
     * @param array $data          The data filtered by the current search,
     * offset and length
     * @param int   $count         The total of available lines filtered by the
     * current search
     * @param int   $filteredCount The total of available lines
     *
     * @return array
     */
    public function getResponse(array $data, $count, $filteredCount)
    {
        return [
            'data' => $this->formateData($data),
            'recordsTotal' => $count,
            'recordsFiltered' => $filteredCount,
        ];
    }

    /**
     * Return the jquery dataTables columns configuration array
     *
     * @return array
     */
    public function getJsColsConfig()
    {
        $cols = [];
        $columns = $this->columnsByIndex;
        foreach ($columns as $ii => $col) {
            $dCol = [
                'orderable'     => (bool) $col->sort,
                'searchable'    => (bool) $col->filter,
                'data'          => $col->name,
                'name'          => $col->name,
                'title'         => $col->label,
            ];

            if (isset($col->width)) {
                $dCol['width'] = $col->width;
            }

            if (isset($col->class)) {
                $dCol['className'] = $col->class;
            }

            $cols[] = $dCol;
        }
        return $cols;
    }

    /**
     * Return the jquery dataTables language configuration array
     *
     * @return array
     *
     * @link http://datatables.net/reference/option/#Internationalisation
     * official documentation
     */
    public function getJsLanguageConfig()
    {
        return [
//            // language.aria : Language strings used for WAI-ARIA specific attributes
//            'aria' => [
//                // language.aria.sortAscending : Language strings used for WAI-ARIA specific attributes
//                'sortAscending'  => null,
//                // language.aria.sortDescending : Language strings used for WAI-ARIA specific attributes
//                'sortDescending' => null,
//            ],
//            // language.decimal : Decimal place character
//            'decimal' => null,
            // language.emptyTable : Table has no records string
            'emptyTable' => 'Aucun ' . $this->config->itemName
                . ' trouvé' . $this->config->itemGenre,
            // language.info : Table summary information display string
            'info' => '' . $this->config->itemsName
                . ' _START_ à  _END_ sur _TOTAL_ ' . $this->config->itemsName,
            // language.infoEmpty : Table summary information string used when the table is empty or records
            'infoEmpty' => 'Aucun ' . $this->config->itemName . '',
            // language.infoFiltered : Appended string to the summary information when the table is filtered
            'infoFiltered' => '(filtre sur _MAX_ ' . $this->config->itemsName . ')',
//            // language.infoPostFix : String to append to all other summary information strings
//            'infoPostFix' => null,
            // language.lengthMenu : Page length options string
            'lengthMenu' => 'Montrer _MENU_ ' . $this->config->itemsName . ' par page',
//            // language.loadingRecords : Loading information display string - shown when Ajax loading data
//            'loadingRecords' => null,
            // language.paginate : Pagination specifarray(ic language strings
            'paginate' => [
                // language.paginate.first : Pagination 'first' button string
                'first' => 'première page',
                // language.paginate.last : Pagination 'last' button string
                'last' => 'dernière page',
                // language.paginate.next : Pagination 'next' button string
                'next' => 'page suivante',
                // language.paginate.previous : Pagination 'previous' button string
                'previous' => 'page précédente',
            ],
            // language.processing : Processing indicator string
            'processing' => 'Chargement',
            // language.search : Search input string
            'search' => 'Recherche',
            // language.searchPlaceholder : Search input element placeholder attribute
            'searchPlaceholder' => 'Recherche',
            // language.thousands : Thousands separator
            'thousands' => '&nbsp;',
            // language.zeroRecords : Table empty as a result of filtering string
            'zeroRecords' => 'Aucun ' . $this->config->itemName,
        ];
    }

    /**
     * The jquery dataTables configuration array
     *
     * @return array
     */
    public function getJsConfig()
    {
        $defaultSort = $this->config->defaultSort;

        $config = [
            'processing' => true,
            'serverSide' => true,
            'ajax'       => [
                'url'  => $this->config->requestUrl,
                'type' => $this->config->requestMethod,
            ],
            'columns'    => $this->getJsColsConfig(),
            'autoWidth'  => true,
            'ordering'   => $defaultSort,
            'jQueryUI'   => true,
            'dom'        => $this->config->dom,
            'language'   => $this->getJsLanguageConfig(),
        ];

        return $config;
    }
}
