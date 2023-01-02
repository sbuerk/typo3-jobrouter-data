<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Synchronisation;

use Brotkrueml\JobRouterData\Domain\Dto\CountResult;
use Brotkrueml\JobRouterData\Domain\Model\Table;
use Brotkrueml\JobRouterData\Domain\Repository\TableRepository;
use Brotkrueml\JobRouterData\Exception\SynchronisationException;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class SynchronisationRunner
{
    private int $totalTables = 0;
    private int $erroneousTables = 0;

    public function __construct(
        private readonly CustomTableSynchroniser $customTableSynchroniser,
        private readonly LoggerInterface $logger,
        private readonly SimpleTableSynchroniser $simpleTableSynchroniser,
        private readonly TableRepository $tableRepository
    ) {
    }

    public function run(string $tableHandle, bool $force): CountResult
    {
        if ($tableHandle === '') {
            $tables = $this->tableRepository->findAll();
            /** @var Table $table */
            foreach ($tables as $table) {
                $this->synchroniseTable($table, $force);
            }
        } else {
            $this->synchroniseTable($this->getTable($tableHandle), $force);
        }

        return new CountResult($this->totalTables, $this->erroneousTables);
    }

    private function synchroniseTable(Table $table, bool $force): void
    {
        $type = $table->getType();

        if ($type === Table::TYPE_SIMPLE) {
            $this->totalTables++;
            if (! $this->simpleTableSynchroniser->synchroniseTable($table, $force)) {
                $this->erroneousTables++;
            }
            return;
        }

        if ($type === Table::TYPE_CUSTOM_TABLE) {
            $this->totalTables++;
            if (! $this->customTableSynchroniser->synchroniseTable($table, $force)) {
                $this->erroneousTables++;
            }
            return;
        }
        if ($type === Table::TYPE_OTHER_USAGE) {
            return;
        }
        if ($type === Table::TYPE_FORM_FINISHER) {
            return;
        }

        $message = \sprintf('Table with uid "%d" has invalid type "%d"!', $table->getUid(), $table->getType());
        $this->logger->error($message);
        $this->erroneousTables++;
    }

    private function getTable(string $tableHandle): Table
    {
        $table = $this->tableRepository->findByHandle($tableHandle);

        if (! $table instanceof Table) {
            $message = \sprintf('Table with handle "%s" not available (perhaps disabled?)!', $tableHandle);
            $this->logger->emergency($message);

            throw new SynchronisationException($message, 1567003394);
        }

        return $table;
    }
}
