<?php

namespace Althingi\Service;

use Althingi\Model;
use Althingi\Hydrator;
use Althingi\Injector\{DatabaseAwareInterface, EventsAwareInterface};
use PDO;

class CommitteeDocument implements DatabaseAwareInterface, EventsAwareInterface
{
    use DatabaseService;
    use EventService;

    public function get(int $id): ? Model\CommitteeDocument
    {
        $statement = $this->getDriver()->prepare("
            select * from `Document_has_Committee`
            where document_committee_id = :document_committee_id
        ");
        $statement->execute(['document_committee_id' => $id]);
        $object = $statement->fetch(PDO::FETCH_ASSOC);

        return $object
            ? (new Hydrator\CommitteeDocument())->hydrate($object, new Model\CommitteeDocument())
            : null;
    }

    /**
     * @return Model\CommitteeDocument[]
     */
    public function fetchByDocument(int $assemblyId, $issueId, $documentId): array
    {
        $statement = $this->getDriver()->prepare("
            select * from `Document_has_Committee`
            where assembly_id = :assembly_id and
                issue_id = :issue_id and
                category = 'A' and
                document_id = :document_id
        ");
        $statement->execute([
            'assembly_id' => $assemblyId,
            'issue_id' => $issueId,
            'category' => 'A',
            'document_id' => $documentId,
        ]);

        return array_map(function ($object) {
            return (new Hydrator\CommitteeDocument())->hydrate($object, new Model\CommitteeDocument());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(Model\CommitteeDocument $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toInsertString('Document_has_Committee', $data)
        );
        $statement->execute($this->toSqlValues($data));

        $id = $this->getDriver()->lastInsertId();
        $data->setDocumentCommitteeId($id);

        return $id;
    }

    public function update(Model\CommitteeDocument $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toUpdateString(
                'Document_has_Committee',
                $data,
                "document_committee_id={$data->getDocumentCommitteeId()}"
            )
        );
        $statement->execute($this->toSqlValues($data));

        return $statement->rowCount();
    }

    public function getIdentifier(
        int $documentId,
        int $assemblyId,
        int $issueId,
        string $category,
        int $committeeId,
        string $part
    ): int {
        $statement = $this->getDriver()->prepare('
            select `committee_sitting_id` from `CommitteeSitting`
            where `assembly_id` = :assembly_id and,
                `document_id` = :document_id and,
                `issue_id` = :issue_id and,
                `category` = :category and,
                `committee_id` = :committee_id and,
                `part` = :part
            ;
        ');
        $statement->execute([
            'document_id' => $documentId,
            'assembly_id' => $assemblyId,
            'issue_id' => $issueId,
            'category' => $category,
            'committee_id' => $committeeId,
            'part' => $part,
        ]);
        return $statement->fetchColumn(0);
    }
}
