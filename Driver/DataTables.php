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
     * Return the filter terms for each columns
     *
     * @return array
     */
    public function getFilterTermByColumns()
    {
        $filteredColumns = [];

        if (empty($this->request)) {
            return $filteredColumns;
        }

        foreach ($this->columns as $index => $column) {
            $clientColumn = $this->request['columns'][$index];
            if (!$clientColumn['searchable']
                || !$column->filter
            ) {
                continue;
            }

            $term = $this->getColumnTerm($clientColumn);
            if ($term === '') {
                continue;
            }

            $sourceFilter = $this->columns->getColumnSourceFilter($column);
            if (!is_array($sourceFilter)) {
                $sourceFilter = [$sourceFilter];
            }

            $filterType = $this->columns->getColumnSourceFilterType($column);

            if ($filterType == 'range_date') {
                $terms = explode($this->conf->delimiter, $term);

                $col = [
                    '',
                    '',
                ];

                if (!empty($terms[0])) {
                    $col[0] = $terms[0];
                }

                if (!empty($terms[1])) {
                    $col[1] = $terms[1];
                }

                $filteredColumns[] = [
                    [$sourceFilter, $col, 'range_date']
                ];
            } else {
                $filteredColumns[] = [
                    [$sourceFilter, $term, 'text']
                ];
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

        if (!isset($this->request['order'])) {
            return $orders;
        }

        $ordersClient = $this->request['order'];
        foreach ($ordersClient as $order) {
            $orders[] = [
                $order['column'],
                $order['dir'],
            ];
        }
        return $orders;
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
    public function getResponse(array $data, $count = null, $filteredCount = null)
    {
        return [
            'data' => $data,
            'recordsTotal' => $count,
            'recordsFiltered' => $filteredCount,
        ];
    }

    /**
     * Return the jquery dataTables columns configuration array
     *
     * @return array
     * @link http://datatables.net/reference/option/#Columns
     * official documentation
     */
    public function getJsColsConfig()
    {
        $cols = [];
        foreach ($this->columns as $ii => $col) {
            $dCol = [
                'orderable'     => (bool) $col->sort,
                'searchable'    => (bool) $col->filter,
                'data'          => $col->name,
                'name'          => $col->name,
                'title'         => $this->columns->getColumnAttribut($col, ['label', 'name']),
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
     * @link http://datatables.net/reference/option/
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

    /**
     * The Yadc pluggin configuration array
     *
     * @return array
     * @link https://github.com/vedmack/yadcf
     */
    public function getYadcfConfig()
    {
        $config = [];

        foreach ($this->columns as $index => $column) {
            if (!$column->filter) {
                continue;
            }

            $config[] = [
                'column_number' => $index,
                'filter_type' => $this->columns->getColumnSourceFilterType($column),
            ];
        }

        return $config;
    }
}
