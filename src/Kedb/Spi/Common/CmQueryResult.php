<?php

namespace Kedb\Spi\Common;


use Kedb\QueryResult;
use Kedb\Spi\SpiQueryResult;

class CmQueryResult implements QueryResult
{
    /**
     * @var SpiQueryResult
     */
    private $queryResult;

    /**
     * @param SpiQueryResult $queryResult
     */
    public function __construct(SpiQueryResult $queryResult)
    {
        $this->queryResult = $queryResult;
    }

    /**
     * @inheritDoc
     */
    public function assoc($column = null)
    {
        $rows = [];
        foreach ($this->queryResult->rows() as $row) {
            if ($column === null) {
                $rows [] = $row;
            } else {
                $rows[$row[$column]] = $row;
            }
        }
        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function row()
    {
        foreach ($this->queryResult->rows() as $row) {
            return $row;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function el($column = null)
    {
        foreach ($this->queryResult->rows() as $row) {
            return $column === null ? current($row) : $row[$column];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function col($column = null)
    {
        $rows = [];
        foreach ($this->queryResult->rows() as $row) {
            $rows [] = $column === null ? current($row) : $row[$column];
        }
        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function ar()
    {
        return $this->queryResult->ar();
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertId()
    {
        return $this->queryResult->getLastInsertId();
    }
}
