<?php
namespace Kedb\PhFormatters;

use Kedb\PhFormatter;
use Kedb\SqlFormatter;

class Literal implements PhFormatter
{

    /**
     * @inheritdoc
     */
    public function format(SqlFormatter $formatter, $value)
    {
        return $formatter->quoteLiteral($value);
    }
}
