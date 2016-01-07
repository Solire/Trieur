<?php
namespace Solire\Trieur\Source\DoctrineOrm;

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
            $terms = [$this->terms];
        } else {
            $terms = $this->terms;
        }


        $conds = [];
        foreach ($terms as $term) {
            foreach ($this->columns as $colName) {
                $cond = $this->queryBuilder->expr()->like($colName, ':word_' . ($index + 1));
                $this->queryBuilder->setParameter('word_' . ($index + 1), $term);
                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
