<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 9/06/15
 * Time: 7:19 PM
 */

namespace Althingi\Model\Dom;

use \PHPUnit_Framework_TestCase;

class AssemblyTest extends PHPUnit_Framework_TestCase
{
    public function testAllElements()
    {
        $dom = new \DOMDocument();
        $root = $dom->createElement('whatever');
        $root->setAttribute('númer', 1);
        $begin = $dom->createElement('þingsetning');
        $begin->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($begin);

        $end = $dom->createElement('þinglok');
        $end->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($end);

        $dom->appendChild($root);

        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertEquals('2002-02-10', $result['to']);
    }

    /**
     * @expectedException \Althingi\Model\Exception
     * @throws \Althingi\Model\Exception
     */
    public function testMissingNumber()
    {
        $dom = new \DOMDocument();
        $root = $dom->createElement('whatever');
        $begin = $dom->createElement('þingsetning');
        $begin->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($begin);

        $end = $dom->createElement('þinglok');
        $end->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($end);

        $dom->appendChild($root);

        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertEquals('2002-02-10', $result['to']);
    }

    /**
     * @expectedException \Althingi\Model\Exception
     * @throws \Althingi\Model\Exception
     */
    public function testMissingFromDate()
    {
        $dom = new \DOMDocument();
        $root = $dom->createElement('whatever');
        $root->setAttribute('númer', 1);

        $end = $dom->createElement('þinglok');
        $end->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($end);

        $dom->appendChild($root);

        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertEquals('2002-02-10', $result['to']);
    }

    /**
     * @expectedException \Althingi\Model\Exception
     * @throws \Althingi\Model\Exception
     */
    public function testNotValidDomElement()
    {

        $root = new \stdClass();
        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertEquals('2002-02-10', $result['to']);
    }

    public function testToCanBeNull()
    {
        $dom = new \DOMDocument();
        $root = $dom->createElement('whatever');
        $root->setAttribute('númer', 1);
        $begin = $dom->createElement('þingsetning');
        $begin->appendChild($dom->createTextNode('10.02.2002'));
        $root->appendChild($begin);

        $dom->appendChild($root);

        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertNull($result['to']);
    }

    public function testDatesInDifferentFormats()
    {
        $dom = new \DOMDocument();
        $root = $dom->createElement('whatever');
        $root->setAttribute('númer', 1);
        $begin = $dom->createElement('þingsetning');
        $begin->appendChild($dom->createTextNode('2002-02-10'));
        $root->appendChild($begin);

        $end = $dom->createElement('þinglok');
        $end->appendChild($dom->createTextNode('03/10/2002'));
        $root->appendChild($end);

        $dom->appendChild($root);

        $model = new Assembly();
        $result = $model->extract($root);

        $this->assertEquals(1, $result['no']);
        $this->assertEquals('2002-02-10', $result['from']);
        $this->assertEquals('2002-03-10', $result['to']);
    }
}
