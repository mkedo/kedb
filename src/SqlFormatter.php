<?php
namespace Kedb;

interface SqlFormatter
{
    /**
     * Return the given value suitably quoted to be used as a string literal in an SQL statement string.
     *
     * @param mixed $value
     * @return string
     */
    public function quoteLiteral($value);

    /**
     * Return the given ident suitably quoted to be used as an identifier in an SQL statement string.
     *
     * @param string|array $ident
     * @return string
     */
    public function quoteIdent($ident);

    /**
     * Return the given binary suitably quoted to be used as binary data in an SQL statement string.
     * @param string $binary
     * @return string
     */
    public function quoteBinary($binary);
}
