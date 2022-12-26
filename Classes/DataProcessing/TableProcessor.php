<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\DataProcessing;

use Brotkrueml\JobRouterData\Cache\Cache;
use Brotkrueml\JobRouterData\Domain\Converter\DatasetConverter;
use Brotkrueml\JobRouterData\Domain\Model\Table;
use Brotkrueml\JobRouterData\Domain\Repository\TableRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * @phpstan-type ProcessedData array{data: array<string, int|string|null>, current: null, table?: Table, rows?: array<int, array<string, float|int|string>>}
 * @internal
 */
final class TableProcessor implements DataProcessorInterface
{
    private ?ContentObjectRenderer $cObj = null;
    private ?array $processedData = null;

    public function __construct(
        private readonly DatasetConverter $datasetConverter,
        private readonly FlexFormService $flexFormService,
        private readonly TableRepository $tableRepository
    ) {
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param ProcessedData $processedData
     * @return array{data: int[]|string[]|null[], current: null, table?: \Brotkrueml\JobRouterData\Domain\Model\Table, rows?: array<int, array<float|int|string>>}
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $this->cObj = $cObj;
        $this->processedData = $processedData;

        $flexForm = $this->flexFormService->convertFlexFormContentToArray($cObj->data['pi_flexform']);
        $tableUid = (int)($flexForm['table'] ?? 0);
        if ($tableUid > 0) {
            $this->enrichProcessedDataWithTableInformation($tableUid);
        }

        return $this->processedData;
    }

    private function enrichProcessedDataWithTableInformation(int $tableUid): void
    {
        $locale = $this->cObj->getRequest()->getAttribute('language')->getLocale();
        /** @var Table $table */
        $table = $this->tableRepository->findByIdentifier($tableUid);
        $this->processedData['table'] = $table;
        $this->processedData['rows'] = $this->datasetConverter->convertFromJsonToArray($table, $locale);
        Cache::addCacheTagByTable($tableUid);
    }
}
