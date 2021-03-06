<?php
namespace Kedb\Spi\Postgresql;

use Kedb\Spi\SpiRows;

class PgRows implements SpiRows
{
    /**
     * @var resource
     */
    private $result;

    /**
     * @var array
     */
    private $row;

    /**
     * @var int
     */
    private $key;

    /**
     * @var bool
     */
    private $fetchedAny;

    /**
     * @param resource $result
     */
    public function __construct($result)
    {
        if (!is_resource($result)) {
            throw new \InvalidArgumentException("\query result must be valid resource");
        }
        $this->result = $result;
    }

    public function __destruct()
    {
        pg_free_result($this->result);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return pg_num_rows($this->result);
    }

    private function fetchRow()
    {
        $this->fetchedAny = true;
        $this->key = $this->key === null ? 0 : ++$this->key;
        $row = pg_fetch_assoc($this->result);
        if ($row === false) {
            $this->row = null;
        } else {
            for ($i = 0; $i < pg_num_fields($this->result); ++$i) {
                $fieldType = pg_field_type($this->result, $i);
                $fieldName = pg_field_name($this->result, $i);
                if (is_null($row[$fieldName])) {
                    continue;
                }
                if ($fieldType === "bytea") {
                    $row[$fieldName] = pg_unescape_bytea($row[$fieldName]);
                } elseif ($fieldType === "bool") {
                    $row[$fieldName] = $row[$fieldName] === "t" ? true : false;
                }
            }
            $this->row = $row;
        }
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->row;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->fetchRow();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->row !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        if ($this->fetchedAny) {
            pg_result_seek($this->result, 0);
            $this->fetchedAny = false;
        }
        $this->fetchRow();
    }
}
