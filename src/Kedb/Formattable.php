<?php
namespace Kedb;


interface Formattable
{
    public function format(SqlFormatter $formatter);
}
