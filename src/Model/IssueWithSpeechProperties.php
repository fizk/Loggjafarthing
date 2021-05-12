<?php

namespace Althingi\Model;

class IssueWithSpeechProperties implements ModelInterface
{
    private SpeechCongressmanProperties $speech;
    private IssueAndDate $issue;

    public function getSpeech(): SpeechCongressmanProperties
    {
        return $this->speech;
    }

    public function setSpeech(SpeechCongressmanProperties $speech): self
    {
        $this->speech = $speech;
        return $this;
    }

    public function getIssue(): IssueAndDate
    {
        return $this->issue;
    }

    public function setIssue(IssueAndDate $issue): self
    {
        $this->issue = $issue;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge($this->issue->toArray(), [
            'speech' => $this->speech
        ]);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
