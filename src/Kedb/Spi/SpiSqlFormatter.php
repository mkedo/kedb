<?php


namespace Kedb\Spi;


interface SpiSqlFormatter
{
    /**
     * @param $ident
     * @return string
     */
    public function formatIdent($ident);

    /**
     * @return string
     */
    public function formatNull();

    /**
     * @param int $value
     * @return string
     */
    public function formatInt($value);

    /**
     * @param float $value
     * @return string
     */
    public function formatFloat($value);

    /**
     * @param bool $value
     * @return string
     */
    public function formatBool($value);

    /**
     * @param string $value
     * @return string
     */
    public function formatString($value);

    /**
     * @return string
     */
    public function formatDefault();

    /**
     * @param string $value
     * @return string
     */
    public function formatBinary($value);
}
