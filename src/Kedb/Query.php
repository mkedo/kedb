<?php
namespace Kedb;

use Kedb\PhProcessors\DefaultProcessor;
use Kedb\Template\PlaceholderTemplate;

class Query implements Formattable
{
    /**
     * @var PlaceholderTemplate
     */
    private $template;

    /**
     * @var array
     */
    private $params;

    /**
     * @var PhProcessor
     */
    private $phProcessor;

    public function __construct($sql, array $params = [])
    {
        $this->template = new PlaceholderTemplate($sql);
        $this->params = $params;
        $this->phProcessor = new DefaultProcessor();
        //@todo: check that number of params matches the number of placeholders
    }

    public function exec(Connection $connection)
    {
        return $connection->plainQuery($this->format($connection->formatter()));
    }

    public function format(SqlFormatter $formatter)
    {
        $data = $this->phProcessor->process($formatter, $this->template->getPlaceholders(), $this->params);
        return $this->template->fetch($data);
    }
}
