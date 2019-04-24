<?php

namespace Althingi\Controller;

use Althingi\Injector\ServiceSearchAssemblyAwareInterface;
use Rend\View\Model\CollectionModel;
use Althingi\Service;
use Zend\Mvc\Controller\AbstractActionController;

class SearchAssemblyController extends AbstractActionController implements
    ServiceSearchAssemblyAwareInterface
{
    /** @var  \Althingi\Service\SearchAssembly */
    private $assemblySearchService;

    /**
     * @return CollectionModel
     * @output \Althingi\Model\Issue[]
     * @query leit
     */
    public function assemblyAction()
    {
        $assemblyId = $this->params('id');
        $query = $this->params()->fromQuery('leit');
        $committees = $this->assemblySearchService->fetchAll($query, $assemblyId);
        $committeesCount = count($committees);

        return (new CollectionModel($committees))
            ->setStatus(206)
            ->setRange(0, $committeesCount, $committeesCount);
    }


    /**
     * @param \Althingi\Service\SearchAssembly $assembly
     * @return $this
     */
    public function setSearchAssemblyService(Service\SearchAssembly $assembly)
    {
        $this->assemblySearchService = $assembly;
        return $this;
    }
}