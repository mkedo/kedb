<?php
namespace Kedb;

interface PhFormatter
{
    /**
     * Returns string that will be replaced with corresponding placeholder.
     *
     * @param SqlFormatter $formatter
     * @param mixed $value
     * @return string
     */
    public function format(SqlFormatter $formatter, $value);
}
