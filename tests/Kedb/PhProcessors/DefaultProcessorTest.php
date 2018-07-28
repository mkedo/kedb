<?php
namespace Kedb\PhProcessors;


use Kedb\Spi\Fake\FakeSqlFormatter;
use Kedb\Template\Placeholder;

class DefaultProcessorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider phProvider
     * @param $placeholders
     * @param $params
     * @param $expected
     * @throws \Kedb\KedbException
     */
    public function testProcess($placeholders, $params, $expected)
    {
        $processor = new DefaultProcessor();

        $processed = $processor->process(new FakeSqlFormatter(), $placeholders, $params);

        $this->assertEquals($expected, $processed);
    }

    public function phProvider()
    {
        return [
            [
                [new Placeholder('id')], ['id' => 1], ['id' => '1'],
            ],
            [
                [new Placeholder('id', 't')], ['id' => 1], ['id' => '"1"'],
            ]
        ];
    }
}
