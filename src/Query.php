<?php
namespace Kedb;


class Query implements Formattable
{
    /**
     * @var QueryTemplate
     */
    private $template;

    /**
     * @var array
     */
    private $params;
    
    public function __construct($sql, array $params = [])
    {
        $this->template = new QueryTemplate($sql);
        $this->params = $params;
    }

    public function exec(Connection $connection)
    {
        return $connection->plainQuery($this->format($connection->formatter()));
    }

    public function format(SqlFormatter $formatter)
    {
        return $this->template->fetch($formatter, $this->params);
    }
}
