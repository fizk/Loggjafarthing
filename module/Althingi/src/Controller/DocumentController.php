<?php

namespace Althingi\Controller;

use Althingi\Injector\ServiceConstituencyAwareInterface;
use Althingi\Injector\StoreDocumentAwareInterface;
use Althingi\Model;
use Althingi\Service;
use Althingi\Form;
use Althingi\Injector\ServiceVoteItemAwareInterface;
use Althingi\Injector\ServiceCongressmanAwareInterface;
use Althingi\Injector\ServiceDocumentAwareInterface;
use Althingi\Injector\ServicePartyAwareInterface;
use Althingi\Injector\ServiceVoteAwareInterface;
use Althingi\Service\Constituency;
use Rend\Controller\AbstractRestfulController;
use Rend\View\Model\CollectionModel;
use Rend\View\Model\EmptyModel;
use Rend\View\Model\ErrorModel;
use Rend\View\Model\ItemModel;

class DocumentController extends AbstractRestfulController implements
    ServiceDocumentAwareInterface,
    ServiceVoteAwareInterface,
    ServiceCongressmanAwareInterface,
    ServicePartyAwareInterface,
    ServiceVoteItemAwareInterface,
    ServiceConstituencyAwareInterface,
    StoreDocumentAwareInterface
{
    /** @var  \Althingi\Service\Document */
    private $documentService;

    /** @var  \Althingi\Store\Document */
    private $documentStore;

    /** @var  \Althingi\Service\Vote */
    private $voteService;

    /** @var  \Althingi\Service\VoteItem */
    private $voteItemService;

    /** @var  \Althingi\Service\Congressman */
    private $congressmanService;

    /** @var  \Althingi\Service\Party */
    private $partyService;

    /** @var  \Althingi\Service\Constituency */
    private $constituencyService;

    /**
     * @param mixed $id
     * @return \Rend\View\Model\ModelInterface
     * @output \Althingi\Model\Document
     * @200 Success
     * @404 Resource not found
     */
    public function get($id)
    {
        $assemblyId = $this->params('id');
        $issueId = $this->params('issue_id');
        $documentId = $this->params('document_id');

        $document = $this->documentService->get($assemblyId, $issueId, $documentId);

        return $document
            ? (new ItemModel($document))->setStatus(200)
            : (new ErrorModel('Resource Not Found'))->setStatus(404);
    }

    /**
     * @return \Rend\View\Model\ModelInterface
     * @output \Althingi\Model\DocumentProperties
     * @206 Success
     */
    public function getList()
    {
        $assemblyId = $this->params('id');
        $issueId = $this->params('issue_id');

        $documents = $this->documentStore->fetchByIssue($assemblyId, $issueId);

        return (new CollectionModel($documents))
            ->setStatus(206)
            ->setRange(0, count($documents), count($documents));
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return \Rend\View\Model\ModelInterface
     * @input \Althingi\Form\Document
     * @201 Created
     * @205 Updated
     * @400 Invalid input
     */
    public function put($id, $data)
    {
        $assemblyId = $this->params('id');
        $issueId = $this->params('issue_id');
        $documentId = $this->params('document_id');

        $form = new Form\Document();
        $form->bindValues(array_merge(
            $data,
            [
                'assembly_id' => $assemblyId,
                'issue_id' => $issueId,
                'document_id' => $documentId,
                'category' => 'A',
            ]
        ));

        if ($form->isValid()) {
            $affectedRows = $this->documentService->save($form->getObject());
            return (new EmptyModel())->setStatus($affectedRows === 1 ? 201 : 205);
        }

        return (new ErrorModel($form))
            ->setStatus(400);
    }

    /**
     * @param $id
     * @param $data
     * @return \Rend\View\Model\ModelInterface
     * @input \Althingi\Form\Document
     * @205 Updated
     * @400 Invalid input
     * @404 Resource not found
     */
    public function patch($id, $data)
    {
        $assemblyId = $this->params('id');
        $issueId = $this->params('issue_id');
        $documentId = $this->params('document_id');

        if (($assembly = $this->documentService->get($assemblyId, $issueId, $documentId)) != null) {
            $form = new Form\Document();
            $form->bind($assembly);
            $form->setData($data);

            if ($form->isValid()) {
                $this->documentService->update($form->getData());
                return (new EmptyModel())
                    ->setStatus(205);
            }

            return (new ErrorModel($form))
                ->setStatus(400);
        }

        return (new ErrorModel('Resource Not Found'))
            ->setStatus(404);
    }

    /**
     * @param \Althingi\Service\Document $document
     * @return $this
     */
    public function setDocumentService(Service\Document $document)
    {
        $this->documentService = $document;
        return $this;
    }

    /**
     * @param \Althingi\Service\Congressman $congressman
     * @return $this
     */
    public function setCongressmanService(Service\Congressman $congressman)
    {
        $this->congressmanService = $congressman;
        return $this;
    }

    /**
     * @param \Althingi\Service\Party $party
     * @return $this
     */
    public function setPartyService(Service\Party $party)
    {
        $this->partyService = $party;
        return $this;
    }

    /**
     * @param \Althingi\Service\Vote $vote
     * @return $this
     */
    public function setVoteService(Service\Vote $vote)
    {
        $this->voteService = $vote;
        return $this;
    }

    /**
     * @param \Althingi\Service\VoteItem $voteItem
     * @return $this
     */
    public function setVoteItemService(Service\VoteItem $voteItem)
    {
        $this->voteItemService = $voteItem;
        return $this;
    }

    /**
     * @param Constituency $constituency
     * @return $this
     */
    public function setConstituencyService(Constituency $constituency)
    {
        $this->constituencyService = $constituency;
        return $this;
    }

    /**
     * @param \Althingi\Store\Document $document
     * @return $this
     */
    public function setDocumentStore(\Althingi\Store\Document $document)
    {
        $this->documentStore = $document;
        return $this;
    }
}
