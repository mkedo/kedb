<?php

namespace Kedb;

interface Connection
{
    /**
     * @param $sql
     * @return QueryResult
     */
    public function plainQuery($sql);

    public function transaction();

    public function close();

    /**
     * @return SqlFormatter
     */
    public function formatter();
}
