<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Synchronisation;

use Brotkrueml\JobRouterData\Domain\Entity\Table;
use Brotkrueml\JobRouterData\Domain\Repository\JobRouter\JobDataRepository;
use Brotkrueml\JobRouterData\Domain\Repository\TableRepository;

/**
 * @internal
 */
class SynchronisationService
{
    public function __construct(
        private readonly JobDataRepository $jobDataRepository,
        private readonly TableRepository $tableRepository,
    ) {
    }

    /**
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function retrieveDatasetsFromJobDataTable(Table $table): array
    {
        return $this->jobDataRepository->findAll($table->handle);
    }

    /**
     * @param list<array<string, string|int|float|bool|null>> $datasets
     */
    public function hashDatasets(array $datasets): string
    {
        return \sha1(\json_encode($datasets, \JSON_THROW_ON_ERROR));
    }

    public function updateSynchronisationStatus(Table $table, string $datasetsHash = '', string $error = ''): void
    {
        $this->tableRepository->updateSynchronisationStatus(
            $table->uid,
            \time(),
            $datasetsHash,
            $error,
        );
    }
}
