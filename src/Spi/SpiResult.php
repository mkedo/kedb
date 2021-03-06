<?php
namespace Kedb\Spi;

interface SpiResult
{
    /**
     * @return SpiRows
     */
    public function rows();

    /**
     * Affected rows.
     *
     * @return int
     */
    public function ar();

    /**
     * Returns the id of the most recently inserted row.
     *
     * @return mixed
     */
    public function getLastInsertId();
}
