<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 27/05/15
 * Time: 7:22 AM
 */

namespace Althingi\Model\Dom;

use Althingi\Lib\IdentityInterface;
use Zend\Stdlib\Extractor\ExtractionInterface;
use Althingi\Model\Exception as ModelException;

class Party implements ExtractionInterface, IdentityInterface
{
    private $id;

    /**
     * Extract values from an object
     *
     * @param  \DOMElement $object
     * @return array|null
     * @throws \Althingi\Model\Exception
     */
    public function extract($object)
    {
        if (!$object instanceof \DOMElement) {
            throw new ModelException('Not a valid \DOMElement');
        }

        if (!$object->hasAttribute('id')) {
            throw new ModelException('Missing [{id}] value');
        }

        $this->setIdentity($object->getAttribute('id'));
        $name = $object->getElementsByTagName('heiti')->item(0)->nodeValue;
        $abbrShort = $object->getElementsByTagName('stuttskammstöfun')->item(0)->nodeValue;
        $abbrLong =  $object->getElementsByTagName('löngskammstöfun')->item(0)->nodeValue . PHP_EOL;

        return [
            'id' => (int) $this->getIdentity(),
            'name' => trim($name),
            'abbr_short' => trim($abbrShort),
            'abbr_long' => trim($abbrLong)
        ];
    }

    public function setIdentity($id)
    {
        $this->id = $id;
    }

    public function getIdentity()
    {
        return $this->id;
    }
}
