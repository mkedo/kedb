<?php
namespace Kedb;


use Kedb\Template\Placeholder;

interface PhProcessor
{
    /**
     * @param SqlFormatter $formatter
     * @param Placeholder[] $placeholders
     * @param $params
     * @return array processed parameters, ready to be inserted in the final query
     */
    public function process(SqlFormatter $formatter, array $placeholders, $params);
}
