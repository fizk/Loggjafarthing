<?php
namespace Althingi\Service;

use Althingi\Lib\DatabaseAwareInterface;
use Althingi\Presenters\IndexableIssueCategoryPresenter;
use PDO;
use Althingi\Model\IssueCategory as IssueCategoryModel;
use Althingi\Hydrator\IssueCategory as IssueCategoryHydrator;
use Althingi\Model\IssueCategoryAndTime as IssueCategoryAndTimeModel;
use Althingi\Hydrator\IssueCategoryAndTime as IssueCategoryAndTimeHydrator;
use Althingi\Lib\EventsAwareInterface;
use Althingi\Events\AddEvent;
use Althingi\Events\UpdateEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class Issue
 * @package Althingi\Service
 */
class IssueCategory implements DatabaseAwareInterface, EventsAwareInterface
{
    use DatabaseService;

    /**
     * @var \PDO
     */
    private $pdo;

    /** @var \Zend\EventManager\EventManagerInterface */
    protected $events;

    /**
     * @param int $assemblyId
     * @param int $issueId
     * @param int $categoryId
     * @return \Althingi\Model\IssueCategory|null
     */
    public function get(int $assemblyId, int $issueId, int $categoryId): ?IssueCategoryModel
    {
        $statement = $this->getDriver()->prepare('
            select * from `Category_has_Issue` C
            where C.`assembly_id` = :assembly_id and C.`issue_id` = :issue_id and C.`category_id` = :category_id
        ');
        $statement->execute([
            'assembly_id' => $assemblyId,
            'issue_id' => $issueId,
            'category_id' => $categoryId
        ]);

        $object = $statement->fetch(PDO::FETCH_ASSOC);
        return $object
            ? (new IssueCategoryHydrator())->hydrate($object, new IssueCategoryModel())
            : null;
    }

    /**
     * Create new Issue. This method
     * accepts object from corresponding Form.
     *
     * @param \Althingi\Model\IssueCategory $data
     * @return int
     */
    public function create(IssueCategoryModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toInsertString('Category_has_Issue', $data)
        );
        $statement->execute($this->toSqlValues($data));
        $this->getEventManager()
            ->trigger(
                AddEvent::class,
                new AddEvent(new IndexableIssueCategoryPresenter($data)),
                ['rows' => $statement->rowCount()]
            );
        return $this->getDriver()->lastInsertId();
    }

    /**
     * @param \Althingi\Model\IssueCategory $data
     * @return int
     */
    public function save(IssueCategoryModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toSaveString('Category_has_Issue', $data)
        );
        $statement->execute($this->toSqlValues($data));
        switch ($statement->rowCount()) {
            case 1:
                $this->getEventManager()
                    ->trigger(
                        AddEvent::class,
                        new AddEvent(new IndexableIssueCategoryPresenter($data)),
                        ['rows' => $statement->rowCount()]
                    );
                break;
            case 0:
            case 2:
                $this->getEventManager()
                    ->trigger(
                        UpdateEvent::class,
                        new UpdateEvent(new IndexableIssueCategoryPresenter($data)),
                        ['rows' => $statement->rowCount()]
                    );
                break;
        }
        return $statement->rowCount();
    }

    /**
     * @param \Althingi\Model\IssueCategory $data
     * @return int
     */
    public function update(IssueCategoryModel $data): int
    {
        $statement = $this->getDriver()->prepare(
            $this->toUpdateString(
                'Category_has_Issue',
                $data,
                "category_id={$data->getCategoryId()} ".
                "and issue_id={$data->getIssueId()} ".
                "and assembly_id={$data->getAssemblyId()}"
            )
        );
        $statement->execute($this->toSqlValues($data));
        $this->getEventManager()
            ->trigger(
                UpdateEvent::class,
                new UpdateEvent(new IndexableIssueCategoryPresenter($data)),
                ['rows' => $statement->rowCount()]
            );
        return $statement->rowCount();
    }

    /**
     * @param int $assemblyId
     * @param int $congressmanId
     * @param array $category
     * @return \Althingi\Model\IssueCategoryAndTime[]
     */
    public function fetchFrequencyByAssemblyAndCongressman(
        int $assemblyId,
        int $congressmanId,
        ?array $category = ['A']
    ): array {
        $categories = count($category) > 0
            ? 'and SP.category in (' . implode(',', array_map(function ($c) {
                return '"' . $c . '"';
            }, $category)) . ')'
            : '';

        $statement = $this->getDriver()->prepare("
            select C.`category_id`, C.`super_category_id`, C.`title`, sum(`speech_sum`) as `time` from (
                select CI.*, TIME_TO_SEC(timediff(SP.`to`, SP.`from`)) as `speech_sum`
                from `Speech` SP
                join `Category_has_Issue` CI on (CI.`issue_id` = SP.`issue_id`)
                where SP.`assembly_id` = :assembly_id and SP.`congressman_id` = :congressman_id {$categories}
            ) as T
            join `Category` C on (C.`category_id` = T.`category_id`)
            group by T.`category_id`
            order by `time` desc;
        ");
        $statement->execute([
            'assembly_id' => $assemblyId,
            'congressman_id' => $congressmanId,
        ]);

        return array_map(function ($object) {
            return (new IssueCategoryAndTimeHydrator())->hydrate($object, new IssueCategoryAndTimeModel());
        }, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param \PDO $pdo
     * @return $this;
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
