<?php

namespace AlthingiTest\Controller;

use Althingi\Controller\PresidentController;
use Althingi\Service\Congressman;
use Althingi\Service\President;
use Althingi\Service\Party;
use AlthingiTest\ServiceHelper;
use Althingi\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

/**
 * Class PresidentControllerTest
 * @package Althingi\Controller
 * @coversDefaultClass \Althingi\Controller\PresidentController
 *
 * @covers \Althingi\Controller\PresidentController::setPresidentService
 * @covers \Althingi\Controller\PresidentController::setPartyService
 * @covers \Althingi\Controller\PresidentController::setCongressmanService
 */
class PresidentControllerTest extends TestCase
{
    use ServiceHelper;

    public function setUp(): void
    {
        $this->setServiceManager(
            new ServiceManager(require __DIR__ . '/../../config/service.php')
        );
        $this->buildServices([
            Party::class,
            President::class,
            Congressman::class,
        ]);
    }

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $this->getMockService(President::class)
            ->shouldReceive('get')
            ->with(1)
            ->andReturn(new \Althingi\Model\President())
            ->once()
            ->getMock();


        $this->dispatch('/forsetar/1');

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('get');
        $this->assertResponseStatusCode(200);
    }

    /**
     * @covers ::get
     */
    public function testGetNotFound()
    {
        $this->getMockService(President::class)
            ->shouldReceive('get')
            ->with(1)
            ->andReturn(null)
            ->once()
            ->getMock();

        $this->dispatch('/forsetar/1');

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('get');
        $this->assertResponseStatusCode(404);
    }

    /**
     * @covers ::getList
     */
    public function testGetList()
    {
        $this->getMockService(President::class)
            ->shouldReceive('fetch')
            ->withNoArgs()
            ->andReturn([
                new \Althingi\Model\President()
            ])
            ->once()
            ->getMock();

        $this->dispatch('/forsetar');

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('getList');
        $this->assertResponseStatusCode(206);
    }

    /**
     * @covers ::post
     */
    public function testPost()
    {
        $autoGeneratedPresidentId = 101;

        $expectedData = (new \Althingi\Model\President())
            ->setPresidentId(0)
            ->setAssemblyId(1)
            ->setCongressmanId(100)
            ->setTitle('some title')
            ->setAbbr('abbr')
            ->setFrom(new \DateTime('2001-01-01'));

        $this->getMockService(President::class)
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($actualData) use ($expectedData) {
                return $actualData == $expectedData;
            }))
            ->andReturn($autoGeneratedPresidentId)
            ->once()
            ->getMock();

        $this->dispatch('/forsetar', 'POST', [
            'assembly_id' => 1,
            'congressman_id' => 100,
            'title' => 'some title',
            'abbr' => 'abbr',
            'from' => '2001-01-01',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('post');
        $this->assertResponseStatusCode(201);

        $this->assertResponseHeaderContains('Location', "/forsetar/{$autoGeneratedPresidentId}");
    }

    /**
     * @covers ::post
     */
    public function testPostAlreadyExists()
    {
        $exception = new \PDOException();
        $exception->errorInfo = ['', 1062, ''];

        $autoGeneratedPresidentId = 1234;

        $expectedData = (new \Althingi\Model\PresidentCongressman())
            ->setPresidentId($autoGeneratedPresidentId)
            ->setAssemblyId(1)
            ->setCongressmanId(100)
            ->setTitle('some title')
            ->setAbbr('abbr')
            ->setFrom(new \DateTime('2001-01-01'));

        $this->getMockService(President::class)
            ->shouldReceive('create')
            ->andThrow($exception)
            ->once()
            ->getMock()

            ->shouldReceive('getByUnique')
            ->andReturn($expectedData)
            ->once()
            ->getMock()
        ;

        $this->dispatch('/forsetar', 'POST', [
            'assembly_id' => 1,
            'congressman_id' => 100,
            'title' => 'some title',
            'abbr' => 'abbr',
            'from' => '2001-01-01',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('post');
        $this->assertResponseStatusCode(409);

        $this->assertResponseHeaderContains('Location', "/forsetar/{$autoGeneratedPresidentId}");
    }

    /**
     * @covers ::post
     */
    public function testPostInvalid()
    {
        $this->getMockService(President::class)
            ->shouldReceive('create')
            ->never()
            ->getMock();

        $this->dispatch('/forsetar', 'POST', [
            'assembly_id' => 1,
            'congressman_id' => 100,
            'title' => 'some title',
            'abbr' => 'abbr',
            'from' => 'invalid-data',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('post');
        $this->assertResponseStatusCode(400);
    }

    /**
     * @covers ::patch
     */
    public function testPatch()
    {
        $autoGeneratedPresidentId = 101;

        $expectedData = (new \Althingi\Model\President())
            ->setPresidentId(0)
            ->setAssemblyId(1)
            ->setCongressmanId(100)
            ->setTitle('another title')
            ->setAbbr('abbr')
            ->setFrom(new \DateTime('2001-01-01'));

        $this->getMockService(President::class)
            ->shouldReceive('get')
            ->with(200)
            ->once()
            ->andReturn((new \Althingi\Model\President())
                ->setPresidentId(0)
                ->setAssemblyId(1)
                ->setCongressmanId(100)
                ->setTitle('old title')
                ->setAbbr('abbr')
                ->setFrom(new \DateTime('2001-01-01')))
            ->getMock()

            ->shouldReceive('update')
            ->with(\Mockery::on(function ($actualData) use ($expectedData) {
                return $actualData == $expectedData;
            }))
            ->andReturn($autoGeneratedPresidentId)
            ->once()
            ->getMock();

        $this->dispatch('/forsetar/200', 'PATCH', [
            'title' => 'another title',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('patch');
        $this->assertResponseStatusCode(205);
    }

    /**
     * @covers ::patch
     */
    public function testPatchInvalidParams()
    {
        $this->getMockService(President::class)
            ->shouldReceive('get')
            ->with(200)
            ->once()
            ->andReturn((new \Althingi\Model\President())
                ->setPresidentId(0)
                ->setAssemblyId(1)
                ->setCongressmanId(100)
                ->setTitle('old title')
                ->setAbbr('abbr')
                ->setFrom(new \DateTime('2001-01-01')))
            ->getMock()

            ->shouldReceive('update')
            ->never()
            ->getMock();

        $this->dispatch('/forsetar/200', 'PATCH', [
            'from' => 'invalid date',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('patch');
        $this->assertResponseStatusCode(400);
    }

    /**
     * @covers ::patch
     */
    public function testPatchNotFound()
    {
        $this->getMockService(President::class)
            ->shouldReceive('get')
            ->with(200)
            ->once()
            ->andReturn(null)
            ->getMock()

            ->shouldReceive('update')
            ->never()
            ->getMock();

        $this->dispatch('/forsetar/200', 'PATCH', [
            'title' => 'another title',
        ]);

        $this->assertControllerName(PresidentController::class);
        $this->assertActionName('patch');
        $this->assertResponseStatusCode(404);
    }
}