<?php

namespace Althingi\Service;

use Althingi\Model;
use Althingi\Hydrator;
use Althingi\Injector\DatabaseAwareInterface;
use Generator;
use PDO;

class Committee implements DatabaseAwareInterface
{
    use DatabaseService;

    public function get(int $id): ? Model\Committee
    {
        $statement = $this->getDriver()->prepare('select * from `Committee` C where C.`committee_id` = :committee_id;');
        $statement->execute(['committee_id' => $id]);

        $object = $statement->fetch(PDO::FETCH_ASSOC);

        return $object
            ? (new Hydrator\Committee())->hydrate($object, new Model\Committee())
            : null;
    }

    /**
     * @return \Althingi\Model\Committee[]
     */
    public function fetchAll(): array
    {
        $statement = $this->getDriver()->prepare('select * from `Committee` C order by C.`name`;');
        $statement->execute();

        return array_map(function ($object) {
            return $object
                ? (new Hydrator\Committee())->hydrate($object, new Model\Committee())
                : null;
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }


    public function fetchAllGenerator(): Generator
    {
        $statement = $this->getDriver()
            ->prepare('select * from `Committee` order by `committee_id`');
        $statement->execute();


        while (($object = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            yield (new Hydrator\Committee)->hydrate($object, new Model\Committee());
        }
        $statement->closeCursor();
        return null;
    }

    /**
     * @return \Althingi\Model\Committee[]
     */
    public function fetchByAssembly(int $assemblyId): array
    {
        $statement = $this->getDriver()->prepare('
            select * from `Committee` C
              where C.`first_assembly_id` <= :assembly_id
              and (C.`last_assembly_id` >= :assembly_id or C.`last_assembly_id` is null)
              order by C.`name`;
        ');

        $statement->execute([
            'assembly_id' => $assemblyId
        ]);

        return array_map(function ($object) {
            return $object
                ? (new Hydrator\Committee())->hydrate($object, new Model\Committee())
                : null;
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(Model\Committee $data): int
    {
        $statement = $this->getDriver()->prepare($this->toInsertString('Committee', $data));
        $statement->execute($this->toSqlValues($data));

        return $this->getDriver()->lastInsertId();
    }

    public function save(Model\Committee $data): int
    {
        $statement = $this->getDriver()->prepare($this->toSaveString('Committee', $data));
        $statement->execute($this->toSqlValues($data));

        return $statement->rowCount();
    }

    public function update(Model\Committee $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toUpdateString('Committee', $data, "committee_id={$data->getCommitteeId()}")
        );
        $statement->execute($this->toSqlValues($data));

        return $statement->rowCount();
    }
}
