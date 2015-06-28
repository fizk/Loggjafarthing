<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 18/05/15
 * Time: 10:30 PM
 */

namespace Althingi\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Session extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct(get_class($this));
        $this
            ->setHydrator(new \Althingi\Hydrator\Session())
            ->setObject((object)[]);

        $this->add(array(
            'name' => 'session_id',
            'type' => 'Zend\Form\Element\Number',
        ));

        $this->add(array(
            'name' => 'congressman_id',
            'type' => 'Zend\Form\Element\Number',
        ));

        $this->add(array(
            'name' => 'constituency_id',
            'type' => 'Zend\Form\Element\Number',
        ));

        $this->add(array(
            'name' => 'assembly_id',
            'type' => 'Zend\Form\Element\Number',
        ));

        $this->add(array(
            'name' => 'from',
            'type' => 'Zend\Form\Element\Date',
        ));

        $this->add(array(
            'name' => 'to',
            'type' => 'Zend\Form\Element\Date',
        ));

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name' => 'party_id',
            'type' => 'Zend\Form\Element\Number',
        ));

    }


    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'session_id' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'congressman_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'constituency_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'assembly_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'from' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'to' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'type' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'party' => [
                'required' => false,
                'allow_empty' => true,
            ],
        ];
    }
}