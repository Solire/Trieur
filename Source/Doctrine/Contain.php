<?php
namespace Solire\Trieur\Source\Doctrine;

class Contain extends Search
{
    public function filter()
    {
        /*
         * Variable qui contient la chaine de recherche
         */
        $stringSearch = trim($this->terms);

        /*
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $conds = [];
        $orderBy     = [];
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

        $this->queryBuilder->andWhere($cond);
        $this->queryBuilder->addOrderBy(implode(' + ', $orderBy), 'DESC');
    }
}
