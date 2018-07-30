<?php
namespace Kedb;


interface Formattable
{
    /**
     * @param SqlFormatter $formatter
     * @return string
     */
    public function format(SqlFormatter $formatter);
}
