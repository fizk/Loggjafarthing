<?php

namespace Althingi\Service;

use Althingi\Model\Party as PartyModel;
use Althingi\Hydrator\Party as PartyHydrator;
use Althingi\Model\PartyAndElection;
use Althingi\Model\PartyAndTime as PartyAndTimeModel;
use Althingi\Hydrator\PartyAndElection as PartyAndElectionHydrator;
use Althingi\Hydrator\PartyAndTime as PartyAndTimeHydrator;
use Althingi\Lib\DatabaseAwareInterface;
use Althingi\Presenters\IndexablePartyPresenter;
use Althingi\ServiceEvents\AddEvent;
use Althingi\ServiceEvents\UpdateEvent;
use PDO;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Class Party
 * @package Althingi\Service
 */
class Party implements DatabaseAwareInterface, EventManagerAwareInterface
{
    use DatabaseService;

    /**
     * @var \PDO
     */
    private $pdo;

    /** @var  \Zend\EventManager\EventManager */
    private $eventManager;

    /**
     * Get one party.
     *
     * @param int $id
     * @return \Althingi\Model\Party|null
     */
    public function get(int $id): ?PartyModel
    {
        $statement = $this->getDriver()->prepare('
            select * from `Party` where party_id = :party_id
        ');
        $statement->execute(['party_id' => $id]);

        $object = $statement->fetch(PDO::FETCH_ASSOC);
        return $object
            ? (new PartyHydrator())->hydrate($object, new PartyModel())
            : null;
    }

    /**
     * @param $congressmanId
     * @param \DateTime $date
     * @return \Althingi\Model\Party|null
     */
    public function getByCongressman(int $congressmanId, \DateTime $date): ?PartyModel
    {
        $statement = $this->getDriver()->prepare('
            select P.* from
            (
                select * from `Session` S where
                (:date between S.`from` and S.`to`) or
                (:date >= S.`from` and S.`to` is null)
            ) S
            Join `Party` P on (P.party_id = S.party_id)
            where S.congressman_id = :congressman_id;
        ');

        $statement->execute([
            'congressman_id' => $congressmanId,
            'date' => $date->format('Y-m-d')
        ]);

        $object = $statement->fetch(PDO::FETCH_ASSOC);
        return $object
            ? (new PartyHydrator())->hydrate($object, new PartyModel())
            : null;
    }

    /**
     * @param $assemblyId
     * @return \Althingi\Model\PartyAndTime[]
     */
    public function fetchTimeByAssembly(int $assemblyId): array
    {
        $statement = $this->getDriver()->prepare('
            select sum(T.`time_sum`) as `total_time`, P.* from (
                select 
                    SP.`congressman_id`, 
                    TIME_TO_SEC(timediff(SP.`to`, SP.`from`)) as `time_sum`,
                    SE.party_id
                from `Speech` SP 
                join `Session` SE ON (SE.`congressman_id` = SP.`congressman_id` and ((SP.`from` between SE.`from` and SE.`to`) or (SP.`from` >= SE.`from` and SE.`to` is null)))
                where SP.`assembly_id` = :assembly_id
            
            ) as T
            join `Party` P on (P.`party_id` = T.`party_id`)
            group by T.`party_id`
            order by `total_time` desc;
        ');
        $statement->execute(['assembly_id' => $assemblyId]);
        return array_map(function ($object) {
            return (new PartyAndTimeHydrator())->hydrate($object, new PartyAndTimeModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param $assemblyId
     * @param array $exclude
     * @return \Althingi\Model\Party[]
     */
    public function fetchByAssembly(int $assemblyId, array $exclude = []): array
    {
        $query = '';
        if (count($exclude) == 0) {
            $query = '
                select P.* from `Session` S
                join `Party` P on (P.`party_id` = S.`party_id`)
                where S.`assembly_id` = :assembly_id
                group by S.`party_id`;
            ';
        } else {
            $query ='
                select P.* from `Session` S
                join `Party` P on (P.`party_id` = S.`party_id`)
                where S.`assembly_id` = :assembly_id and P.`party_id` not in ('.implode(',', $exclude).')
                group by S.`party_id`;
            ';
        }

        $statement = $this->getDriver()->prepare($query);
        $statement->execute(['assembly_id' => $assemblyId]);
        return array_map(function ($object) {
            return (new PartyHydrator())->hydrate($object, new PartyModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param int $assemblyId
     * @return \Althingi\Model\PartyAndElection[]
     */
    public function fetchElectedByAssembly(int $assemblyId): array
    {
        $statement = $this->getDriver()->prepare('
            select * from `ElectionResult` ER join `Election_has_Assembly` E on (E.`election_id` = ER.`election_id`)
            join `Party` P on (P.party_id = ER.party_id)
            where E.`assembly_id` = :assembly_id order by ER.`result` desc;
        ');
        $statement->execute(['assembly_id' => $assemblyId]);
        return array_map(function ($object) {
            return (new PartyAndElectionHydrator())->hydrate($object, new PartyAndElection());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get all parties that a congressman as been in.
     *
     * @param int $congressmanId
     * @return \Althingi\Model\Party[]
     */
    public function fetchByCongressman(int $congressmanId): array
    {
        $statement = $this->getDriver()->prepare(
            'select P.* from `Session` S
            join `Party` P on (P.party_id = S.party_id)
            where congressman_id = :congressman_id group by `party_id`;'
        );
        $statement->execute(['congressman_id' => $congressmanId]);
        return array_map(function ($object) {
            return (new PartyHydrator())->hydrate($object, new PartyModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param int $cabinetId
     * @return \Althingi\Model\Party[]
     */
    public function fetchByCabinet(int $cabinetId): array
    {
        $statement = $this->getDriver()->prepare('
            select P.* from `Cabinet_has_Congressman` CC
            join `Session` SE ON (SE.`congressman_id` = CC.`congressman_id` and ((CC.`from` between SE.`from` and SE.`to`) or (CC.`from` >= SE.`from` and SE.`to` is null)))
            join `Party` P on (SE.`party_id` = P.`party_id`)
            where cabinet_id = :cabinet_id
            group by SE.`party_id`;    
        ');
        $statement->execute(['cabinet_id' => $cabinetId]);
        return array_map(function ($object) {
            return (new PartyHydrator())->hydrate($object, new PartyModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Create one Party. Accept object from corresponding
     * Form.
     *
     * @param \Althingi\Model\Party $data
     * @return int
     */
    public function create(PartyModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toInsertString('Party', $data)
        );
        $statement->execute($this->toSqlValues($data));

        $this->getEventManager()->trigger(new AddEvent(new IndexablePartyPresenter($data)));

        return $this->getDriver()->lastInsertId();
    }

    /**
     * @param \Althingi\Model\Party $data
     * @return int
     */
    public function save(PartyModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toSaveString('Party', $data)
        );
        $statement->execute($this->toSqlValues($data));

        $this->getEventManager()->trigger(new AddEvent(new IndexablePartyPresenter($data)));

        return $statement->rowCount();
    }

    /**
     * @param \Althingi\Model\Party $data
     * @return int
     */
    public function update(PartyModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toUpdateString('Party', $data, "party_id={$data->getPartyId()}")
        );
        $statement->execute($this->toSqlValues($data));

        $this->getEventManager()->trigger(new UpdateEvent(new IndexablePartyPresenter($data)));

        return $statement->rowCount();
    }

    /**
     * @param \PDO $pdo
     */
    public function setDriver(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getDriver()
    {
        return $this->pdo;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
