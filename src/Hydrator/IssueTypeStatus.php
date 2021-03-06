<?php

namespace Althingi\Hydrator;

use Laminas\Hydrator\HydratorInterface;

class IssueTypeStatus implements HydratorInterface
{
    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  \Althingi\Model\IssueTypeStatus $object
     * @return \Althingi\Model\IssueTypeStatus
     */
    public function hydrate(array $data, $object)
    {
        return $object
            ->setCount($data['count'])
            ->setStatus($data['status']);
    }

    /**
     * Extract values from an object
     *
     * @param  \Althingi\Model\IssueTypeStatus $object
     * @return array
     */
    public function extract($object): array
    {
        unset($object->time);

        return $object->toArray();
    }
}
