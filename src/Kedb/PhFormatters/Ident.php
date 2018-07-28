<?php
namespace Kedb\PhFormatters;

use Kedb\PhFormatter;
use Kedb\SqlFormatter;

class Ident implements PhFormatter
{
    /**
     * @inheritdoc
     */
    public function format(SqlFormatter $formatter, $value)
    {
        return $formatter->quoteIdent($value);
    }
}
