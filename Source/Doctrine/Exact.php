<?php

namespace Solire\Trieur\Source\Doctrine;

/**
 * Doctrine filter class for Exact filter.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Exact extends Filter
{
    /**
     * Filter.
     *
     * @return void
     */
    public function filter()
    {
        /*
         * Variable qui contient la chaine de recherche
         */
        if (!is_array($this->terms)) {
            $terms = [$this->terms];
        } else {
            $terms = $this->terms;
        }

        $conds = [];
        foreach ($terms as $term) {
            foreach ($this->columns as $colName) {
                $cond = $colName . ' = '
                      . $this->queryBuilder->getConnection()->quote($term);
                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
