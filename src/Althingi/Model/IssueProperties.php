<?php

namespace Althingi\Model;

class IssueProperties implements ModelInterface
{
    /** @var  \Althingi\Model\Issue */
    private $issue;

    /** @var  \Althingi\Model\CongressmanPartyProperties[] */
    private $proponents;

    /** @var  \Althingi\Model\DateAndCount[] */
    private $voteRange;

    /** @var  \Althingi\Model\DateAndCount[] */
    private $speechRange;

    /** @var  \Althingi\Model\CongressmanAndDateRange[] */
    private $speakers;

    /**
     * @return Issue
     */
    public function getIssue(): Issue
    {
        return $this->issue;
    }

    /**
     * @param Issue $issue
     * @return IssueProperties
     */
    public function setIssue(Issue $issue): IssueProperties
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * @return array
     */
    public function getProponents(): array
    {
        return $this->proponents;
    }

    /**
     * @param CongressmanPartyProperties[] $proponents
     * @return IssueProperties
     */
    public function setProponents(array $proponents): IssueProperties
    {
        $this->proponents = $proponents;
        return $this;
    }

    /**
     * @return DateAndCount[]
     */
    public function getVoteRange(): array
    {
        return $this->voteRange;
    }

    /**
     * @param DateAndCount[] $voteRange
     * @return IssueProperties
     */
    public function setVoteRange(array $voteRange): IssueProperties
    {
        $this->voteRange = $voteRange;
        return $this;
    }

    /**
     * @return DateAndCount[]
     */
    public function getSpeechRange(): array
    {
        return $this->speechRange;
    }

    /**
     * @param DateAndCount[] $speechRange
     * @return IssueProperties
     */
    public function setSpeechRange(array $speechRange): IssueProperties
    {
        $this->speechRange = $speechRange;
        return $this;
    }

    /**
     * @return CongressmanAndDateRange[]
     */
    public function getSpeakers(): array
    {
        return $this->speakers;
    }

    /**
     * @param CongressmanAndDateRange[] $speakers
     * @return IssueProperties
     */
    public function setSpeakers(array $speakers): IssueProperties
    {
        $this->speakers = $speakers;
        return $this;
    }

    public function toArray()
    {
        return array_merge(
            $this->issue->toArray(),
            [
                'proponents' => $this->proponents,
                'voteRange' => $this->voteRange,
                'speechRange' => $this->speechRange,
                'speakers' => $this->speakers,
            ]
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
