<?php

namespace Althingi\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class Vote extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct(get_class($this));
        $this
            ->setHydrator(new \Althingi\Hydrator\Vote())
            ->setObject(new \Althingi\Model\Vote());

        $this->add([
            'name' => 'vote_id',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'assembly_id',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'document_id',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'issue_id',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'category',
            'type' => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name' => 'date',
            'type' => 'Zend\Form\Element\DateTime',
            'options' => [
                'format' => 'Y-m-d H:i:s',

            ],
            'attributes' => [
                'step' => 'any'
            ],
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name' => 'outcome',
            'type' => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name' => 'method',
            'type' => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name' => 'yes',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'no',
            'type' => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name' => 'inaction',
            'type' => 'Zend\Form\Element\Number',
        ]);
        $this->add([
            'name' => 'committee_to',
            'type' => 'Zend\Form\Element\Text',
        ]);
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
            'issue_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'assembly_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'category' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'document_id' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToNull',
                        'options' => ['type' => 'all']
                    ]
                ],
            ],
            'vote_id' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'date' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'type' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'outcome' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToNull',
                        'options' => ['type' => 'all']
                    ]
                ],
            ],
            'method' => [
                'required' => true,
                'allow_empty' => false,
            ],
            'yes' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToInt',
                    ]
                ],
            ],
            'no' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToInt',
                    ]
                ],
            ],
            'inaction' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToInt',
                    ]
                ],
            ],
            'committee_to' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => '\Zend\Filter\ToNull',
                        'options' => ['type' => 'all']
                    ]
                ],
            ],
        ];
    }
}