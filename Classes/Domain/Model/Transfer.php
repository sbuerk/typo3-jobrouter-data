<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Transfer extends AbstractEntity
{
    protected int $crdate = 0;
    protected int $tableUid = 0;
    protected string $correlationId = '';
    protected string $data = '';
    protected bool $transmitSuccess = false;
    protected ?\DateTime $transmitDate = null;
    protected string $transmitMessage = '';

    public function getCrdate(): int
    {
        return $this->crdate;
    }

    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }

    public function getTableUid(): int
    {
        return $this->tableUid;
    }

    public function setTableUid(int $tableUid): void
    {
        $this->tableUid = $tableUid;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function isTransmitSuccess(): bool
    {
        return $this->transmitSuccess;
    }

    public function setTransmitSuccess(bool $transmitSuccess): void
    {
        $this->transmitSuccess = $transmitSuccess;
    }

    public function getTransmitDate(): ?\DateTime
    {
        return $this->transmitDate;
    }

    public function setTransmitDate(\DateTime $transmitDate): void
    {
        $this->transmitDate = $transmitDate;
    }

    public function getTransmitMessage(): string
    {
        return $this->transmitMessage;
    }

    public function setTransmitMessage(string $transmitMessage): void
    {
        $this->transmitMessage = $transmitMessage;
    }
}
