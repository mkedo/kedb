<?php
namespace Kedb;


interface QueryResult
{
    /**
     * @param string|int $column
     * @return array
     */
    public function assoc($column = null);

    /**
     * @return array
     */
    public function row();

    /**
     * @param string|int $column
     * @return mixed|null
     */
    public function el($column = null);

    /**
     * @param string|int $column
     * @return mixed|null
     */
    public function col($column = null);

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

    /**
     * Returns the number of rows in a result.
     *
     * @return int
     */
    public function getNumRows();
}
