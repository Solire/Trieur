<?php
namespace Solire\Trieur\Source\DoctrineOrm;

/**
 * Doctrine filter class for DateRange filter
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class DateRange extends Filter
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

    const MASK = '#^\d{4}\-\d{2}\-\d{2}$#';

    /**
     * Constructor
     *
     * @param mixed $columns Column
     * @param array $terms   Terms to search for
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
        if (preg_match(self::MASK, $this->from)) {
            $cond = $this->queryBuilder->expr()->gte($this->columns[0], $this->from);
            $this->queryBuilder->andWhere($cond);
        }

        if (preg_match(self::MASK, $this->to)) {
            $cond = $this->queryBuilder->expr()->lte($this->columns[0], $this->to);
            $this->queryBuilder->andWhere($cond);
        }
    }
}
