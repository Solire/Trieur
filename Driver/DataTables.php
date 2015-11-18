<?php
namespace Solire\Trieur\Driver;

use Solire\Trieur\Driver;
use Solire\Conf\Conf;

/**
 * Datatables driver
 *
 * @author  thansen <thansen@solire.fr>
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
     * Determines if a column is filterable and has a filter, if so return the
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
    public function getFilters()
    {
        $filteredColumns = [];
        $allSourceFilter = [];

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

            $filterType = $column->sourceFilterType;

            $sourceFilter = $column->sourceFilter;
            if (!is_array($sourceFilter)) {
                $sourceFilter = [$sourceFilter];
            }

            if ($filterType == 'Contain') {
                $allSourceFilter = array_merge($allSourceFilter, $sourceFilter);
            }

            $term = $this->getColumnTerm($clientColumn);
            if ($term === '') {
                continue;
            }

            if (isset($this->config->separator)
                && !empty($this->config->separator)
            ) {
                $terms = explode($this->config->separator, $term);
            } else {
                $terms = [$term];
            }

            $filteredColumns[] = [$sourceFilter, $terms, $filterType];
        }

        if ($this->getFilterTerm() !== '') {
            $filteredColumns[] = [$allSourceFilter, $this->getFilterTerm(), 'Contain'];
        }

        return $filteredColumns;
    }

    /**
     * Return the number of lines
     *
     * @return int
     */
    public function getLength()
    {
        return $this->request['length'];
    }

    /**
     * Return the offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->request['start'];
    }

    /**
     * Return the list of columns for the sort with the direction
     *
     * @return array
     */
    public function getOrder()
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
     * @param array $data          The data filtered by the current filters,
     * offset and length, sorted by the currents orders
     * @param int   $count         The total of available lines filtered by the
     * current filters
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
        foreach ($this->columns as $ii => $column) {
            $dCol = [
                'orderable' => (bool) $column->sort,
                'searchable' => (bool) $column->filter,
                'data' => $column->name,
                'name' => $column->name,
                'title' => $column->label,
            ];

            if (isset($column->driverHidden) && $column->driverHidden) {
                $dCol['visible']   = false;
                $dCol['className'] = 'never';

            }

            if (isset($column->width)) {
                $dCol['width'] = $column->width;
            }

            if (isset($column->class)) {
                $dCol['className'] = $column->class;
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
     * @link http://datatables.net/reference/option/ official documentation
     */
    public function getJsConfig()
    {
        $config = [
            'processing' => true,
            'serverSide' => true,
            'ajax'       => [
                'url'  => $this->config->requestUrl,
                'type' => $this->config->requestMethod,
            ],
            'columns'    => $this->getJsColsConfig(),
            'language'   => $this->getJsLanguageConfig(),
        ];

        if (isset($this->config->defaultSort)) {
            $config['order'] = static::objectToArray($this->config->defaultSort);
        }
        if (isset($this->config->autoWidth)) {
            $config['autoWidth'] = $this->config->autoWidth;
        }
        if (isset($this->config->dom)) {
            $config['dom'] = $this->config->dom;
        }

        if (!empty($this->config->config)) {
            $config = array_merge($config, (array) $this->config->config);
        }

        return $config;
    }

    /**
     * The jquery dataTables light columnfilter configuration array
     *
     * @return array
     */
    public function getColumnFilterConfig()
    {
        $config = [];

        foreach ($this->columns as $index => $column) {
            if (isset($column->driverHidden) && $column->driverHidden) {
                continue;
            }

            if (!$column->filter) {
                continue;
            }

            $columnConfig = [];
            $columnConfig['type'] = $column->driverFilterType;
            if (isset($column->driverOption)) {
                $columnConfig = array_merge($columnConfig, self::objectToArray($column->driverOption));
            }

            $config[$index] = $columnConfig;
        }

        return $config;
    }

    /**
     * Cast a PHP object to array recursively
     *
     * @param object $obj Object to cast
     *
     * @return array
     *
     * @todo Move to a trait or thing like this
     */
    private static function objectToArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array) $obj;
        }
        if (is_array($obj)) {
            $new = [];
            foreach ($obj as $key => $val) {
                $new[$key] = self::objectToArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }
}
