<?php
namespace Kedb\Template;

class Placeholder
{
    /**
     * @var int|string
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @param int|string $name
     * @param string $type
     */
    public function __construct($name, $type = '')
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return int|string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function type()
    {
        return $this->type;
    }
}
