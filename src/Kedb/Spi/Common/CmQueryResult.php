<?php

namespace Kedb\Spi\Common;


use Kedb\QueryResult;
use Kedb\Spi\SpiResult;

class CmQueryResult implements QueryResult
{
    /**
     * @var SpiResult
     */
    private $spiResult;

    /**
     * @param SpiResult $spiResult
     */
    public function __construct(SpiResult $spiResult)
    {
        $this->spiResult = $spiResult;
    }

    /**
     * @inheritDoc
     */
    public function assoc($column = null)
    {
        $rows = [];
        foreach ($this->spiResult->rows() as $row) {
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
        foreach ($this->spiResult->rows() as $row) {
            return $row;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function el($column = null)
    {
        foreach ($this->spiResult->rows() as $row) {
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
        foreach ($this->spiResult->rows() as $row) {
            $rows [] = $column === null ? current($row) : $row[$column];
        }
        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function ar()
    {
        return $this->spiResult->ar();
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertId()
    {
        return $this->spiResult->getLastInsertId();
    }

    /**
     * @inheritDoc
     */
    public function getNumRows()
    {
        return count($this->spiResult->rows());
    }
}
