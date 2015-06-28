<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 20/05/15
 * Time: 7:40 AM
 */

namespace Althingi\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class SessionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ .'/../application.config.php'
        );
        parent::setUp();
    }

    public function testCreateSuccess()
    {
        $serviceMock = \Mockery::mock('Althingi\Service\Session')
            ->shouldReceive('create')
            ->andReturnUsing(function ($object) {
                $this->assertEquals(2, $object->congressman_id);
                $this->assertEquals(4, $object->party_id);
                return 10;
            })->getMock();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Althingi\Service\Session', $serviceMock);

        $this->dispatch('/api/thingmenn/2/thingseta', 'POST', [
            'constituency_id' => 1,
            'assembly_id' => 1,
            'from' => '2010-01-01',
            'to' => '2010-01-01',
            'type' => 'varamadur',
            'party_id' => 4,
        ]);

        $this->assertResponseStatusCode(201);
        $this->assertResponseHeaderContains('Location', '/api/thingmenn/2/thingseta/10');
        $this->assertControllerClass('SessionController');
        $this->assertActionName('create');
    }

    public function testCreateInvalid()
    {
        $serviceMock = \Mockery::mock('Althingi\Service\Session')
            ->shouldReceive('create')
            ->andReturnNull()
            ->getMock();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Althingi\Service\Session', $serviceMock);

        $this->dispatch('/api/thingmenn/2/thingseta', 'POST', [
            'constituency_id' => 1,
            'from' => 'not-valid-date',
            'to' => '2010-01-01',
            'type' => 'varamadur',
            'party_id' => 2,
        ]);

        $this->assertResponseStatusCode(400);
        $this->assertControllerClass('SessionController');
        $this->assertActionName('create');
    }

    public function testGetList()
    {
        $serviceMock = \Mockery::mock('Althingi\Service\Session')
            ->shouldReceive('fetchByCongressman')
            ->andReturn([])
            ->getMock();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Althingi\Service\Session', $serviceMock);

        $this->dispatch('/api/thingmenn/2/thingseta', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('SessionController');
        $this->assertActionName('get');
    }
}
