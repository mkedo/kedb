<?php
namespace Kedb\PhFormatters;

use Kedb\PhFormatter;
use Kedb\SqlFormatter;

class Raw implements PhFormatter
{
    /**
     * @inheritDoc
     */
    public function format(SqlFormatter $formatter, $value)
    {
        return $value;
    }
}
