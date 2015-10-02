<?php
namespace Solire\Trieur\Source\Doctrine;

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
        $orderBy = [];
        foreach ($words as $word) {
            foreach ($this->columns as $colName) {
                /**
                 * @todo add a ponderation array to the constructor
                 */
                $pond    = 1;

                $cond = $colName . ' LIKE '
                      . $this->queryBuilder->getConnection()->quote('%' . $word . '%');
                $conds[] = $cond;
                $orderBy[] = 'IF(' . $cond . ', ' . mb_strlen($word) * $pond . ', 0)';
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
        $this->queryBuilder->addOrderBy(implode(' + ', $orderBy), 'DESC');
    }
}
