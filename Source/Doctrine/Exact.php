<?php
namespace Solire\Trieur\Source\Doctrine;

class Exact extends Filter
{
    public function filter()
    {
        foreach ($this->columns as $colName) {
            $cond = $colName . ' = '
                  . $this->queryBuilder->getConnection()->quote($this->terms);
            $this->queryBuilder->andWhere($cond);
        }
    }
}
