<?php
namespace Solire\Trieur\Source\Doctrine;

use Doctrine\DBAL\Query\QueryBuilder;

class DateRange extends Search
{
    /**
     * Column containing the date
     *
     * @var string
     */
    protected $column;

    /**
     * Start's date
     *
     * @var string
     */
    protected $from;

    /**
     * End's date
     *
     * @var string
     */
    protected $to;

    /**
     * QueryBuilder
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    const MASK = '#^\d{4}\-\d{2}\-\d{2}$#';

    /**
     * Constructor
     *
     * @param string $column Column
     * @param array  $terms  Terms to search for
     */
    public function __construct($columns, array $terms)
    {
        $this->columns = $columns;
        list($this->from, $this->to) = $terms;
    }

    /**
     * Add a filter to the query builder
     *
     * @return void
     */
    public function filter()
    {
        $conds = [];

        if (preg_match(self::MASK, $this->from)) {
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->gte(
                    $this->columns[0],
                    $this->queryBuilder->getConnection()->quote($this->from)
                )
            );
        }

        if (preg_match(self::MASK, $this->to)) {
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->lte(
                    $this->columns[0],
                    $this->queryBuilder->getConnection()->quote($this->to)
                )
            );
        }
    }
}
