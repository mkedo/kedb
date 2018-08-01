<?php
namespace Kedb\PhFormatters;

use Kedb\PhFormatter;
use Kedb\SqlFormatter;

class Binary implements PhFormatter
{
    /**
     * @inheritDoc
     */
    public function format(SqlFormatter $formatter, $value)
    {
        return $formatter->quoteBinary($value);
    }
}

