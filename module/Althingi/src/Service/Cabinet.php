<?php

namespace Althingi\Service;

use Althingi\Lib\DatabaseAwareInterface;
use Althingi\Model\Cabinet as CabinetModel;
use Althingi\Hydrator\Cabinet as CabinetHydrator;
use Althingi\Lib\EventsAwareInterface;
use Althingi\Presenters\IndexableCabinetPresenter;
use Althingi\ServiceEvents\AddEvent;
use Althingi\ServiceEvents\UpdateEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use PDO;

/**
 * Class Assembly
 * @package Althingi\Service
 */
class Cabinet implements DatabaseAwareInterface, EventsAwareInterface
{
    use DatabaseService;

    /** @var  \PDO */
    private $pdo;

    /** @var \Zend\EventManager\EventManagerInterface */
    protected $events;

    public function fetchAll(?\DateTime $from = null, ?\DateTime $to = null)
    {
        if ($from !== null && $to === null) {
            $statement = $this->getDriver()->prepare(
                "select * from `Cabinet`
                where `from` <= :from 
                order by `from`"
            );
            $statement->execute(['from' => $from->format('Y-m-d')]);
        } elseif ($from !== null && $to !== null) {
            $statement = $this->getDriver()->prepare(
                "select * from `Cabinet` 
                where `from` <= :from and `to` >= :to
                order by `from`"
            );
            $statement->execute([
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ]);
        } elseif ($from === null && $to !== null) {
            $statement = $this->getDriver()->prepare(
                "select * from `Cabinet` 
                where `to` >= :to
                order by `from`"
            );
            $statement->execute(['to' => $to->format('Y-m-d')]);
        } else {
            $statement = $this->getDriver()->prepare(
                "select * from `Cabinet` order by `from`"
            );
            $statement->execute();
        }

        return array_map(function ($object) {
            return (new CabinetHydrator)->hydrate($object, new CabinetModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param int $id
     * @return \Althingi\Model\Cabinet
     */
    public function get(int $id)
    {
        $statement = $this->getDriver()->prepare("select * from `Cabinet` where cabinet_id = :id");
        $statement->execute(['id' => $id]);
        $object = $statement->fetch(PDO::FETCH_ASSOC);

        return $object ? (new CabinetHydrator())
            ->hydrate($object, new CabinetModel())
            : null;
    }

    /**
     * @param \Althingi\Model\Cabinet $data
     * @return int
     */
    public function save(CabinetModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toSaveString('Cabinet', $data)
        );
        $statement->execute($this->toSqlValues($data));

        $this->getEventManager()
            ->trigger(AddEvent::class, new AddEvent(new IndexableCabinetPresenter($data)));

        return $statement->rowCount();
    }

    /**
     * @param \Althingi\Model\Cabinet $data
     * @return int
     */
    public function update(CabinetModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toUpdateString('Cabinet', $data, "cabinet_id={$data->getCabinetId()}")
        );
        $statement->execute($this->toSqlValues($data));

        $this->getEventManager()
            ->trigger(UpdateEvent::class, new UpdateEvent(new IndexableCabinetPresenter($data)));

        return $statement->rowCount();
    }

    /**
     * @param int $assemblyId
     * @return \Althingi\Model\Cabinet[]
     */
    public function fetchByAssembly(int $assemblyId): array
    {
        $statement = $this->getDriver()->prepare("
            select * from (
                select 
                    A.`assembly_id`, 
                    C.`cabinet_id`, 
                    C.`title`,
                    C.`description`,
                    C.`from`,
                    C.`to`,
                    A.`from` as `assembly_from`,
                    A.`to` as `assembly_to`
                from Assembly A
                join Cabinet C on (
                    (A.`to` between C.`from` and C.`to`) or
                    (A.`to` > C.`from` and C.`to` is null) or
                    (A.`to` is null and C.`to` is null)
                  )
            ) as AssembliesAndCabinets where assembly_id = :assembly_id;
        ");

        $statement->execute(['assembly_id' => $assemblyId]);

        return array_map(function ($object) {
            return (new CabinetHydrator())->hydrate($object, new CabinetModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param \PDO $pdo
     * @return $this
     */
    public function setDriver(PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @return \PDO
     */
    public function getDriver()
    {
        return $this->pdo;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
}
