<?php
namespace Solire\Trieur\Source\Doctrine;

class Exact extends Filter
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
        if (!is_array($this->terms)) {
            $stringSearch = [$this->terms];
        } else {
            $stringSearch = $this->terms;
        }


        $conds = [];
        foreach ($this->terms as $term) {
            foreach ($this->columns as $colName) {
                $cond = $colName . ' = '
                      . $this->queryBuilder->getConnection()->quote($term);
                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
