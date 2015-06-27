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

class Assembly implements ExtractionInterface, IdentityInterface
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

        if (!$object->hasAttribute('númer')) {
            throw new ModelException('Missing [{númer}] value');
        }

        if (!$object->getElementsByTagName('þingsetning')->item(0)) {
            throw new ModelException('Missing [{þingsetning}] value');
        }

        $this->setIdentity($object->getAttribute('númer'));

        $from = date(
            'Y-m-d',
            strtotime($object->getElementsByTagName('þingsetning')->item(0)->nodeValue)
        );
        $to = ($object->getElementsByTagName('þinglok')->item(0))
            ? date('Y-m-d', strtotime($object->getElementsByTagName('þinglok')->item(0)->nodeValue))
            : null ;

        return [
            'no' => (int) $this->getIdentity(),
            'from' => $from,
            'to' => $to
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
