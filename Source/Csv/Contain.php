<?php
namespace Solire\Trieur\Source\Csv;

/**
 * Description of Contain
 *
 * @author thansen
 */
class Contain extends Search
{
    public function filter()
    {
        if (is_array($this->terms)) {
            $term = implode(' ', $this->terms);
        } else {
            $term = $this->terms;
        }

        $words = preg_split('`\s+`', $term);
        foreach ($words as $word) {
            foreach ($this->columns as $column) {
                if (stripos($this->row[$column], $word) !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
