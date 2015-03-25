<?php
namespace Solire\Trieur\Source\Doctrine;

/**
 * Doctrine search class for Contain filter
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Contain extends Search
{
    /**
     * Filter
     *
     * @return void
     */
    public function filter()
    {
        /*
         * Variable qui contient la chaine de recherche
         */
        if (is_array($this->terms)) {
            $stringSearch = implode(' ', $this->terms);
        } else {
            $stringSearch = $this->terms;
        }

        /*
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $conds = [];
        $orderBy = [];
        foreach ($words as $word) {
            foreach ($this->columns as $key => $value) {
                if (is_numeric($value)) {
                    $pond    = $value;
                    $colName = $key;
                } else {
                    $pond    = 1;
                    $colName = $value;
                }

                $cond = $colName . ' LIKE '
                      . $this->queryBuilder->getConnection()->quote('%' . $word . '%');
                $conds[] = $cond;
                $orderBy[]      = 'IF(' . $cond . ', ' . mb_strlen($word) * $pond . ', 0)';
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
        $this->queryBuilder->addOrderBy(implode(' + ', $orderBy), 'DESC');
    }
}
