<?php

namespace Solire\Trieur\Source\DoctrineOrm;

/**
 * Doctrine filter class for Contain filter
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Contain extends Filter
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

        $words = array_unique($words);

        $conds = [];
        foreach ($words as $index => $word) {
            foreach ($this->columns as $colName) {
                $cond = $this->queryBuilder->expr()->like($colName, ':word_' . ($index + 1));
                $this->queryBuilder->setParameter('word_' . ($index + 1), '%' . $word . '%');

                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
