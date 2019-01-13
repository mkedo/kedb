<?php
namespace Kedb\PhFormatters;

use Kedb\KedbException;
use Kedb\PhFormatter;
use Kedb\SqlFormatter;

class IdentList implements PhFormatter
{
    /**
     * @inheritDoc
     */
    public function format(SqlFormatter $formatter, $value)
    {
        if (!is_array($value) || empty($value)) {
            throw new KedbException("Value must be an non-empty array");
        }

        return implode(
            ',',
            array_map(
                function ($ident) use ($formatter) {
                    return $formatter->quoteIdent($ident);
                },
                $value
            )
        );
    }
}
