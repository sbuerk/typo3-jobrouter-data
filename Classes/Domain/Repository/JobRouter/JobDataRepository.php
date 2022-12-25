<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Domain\Repository\JobRouter;

use Brotkrueml\JobRouterClient\Client\ClientInterface;
use Brotkrueml\JobRouterClient\Exception\ExceptionInterface;
use Brotkrueml\JobRouterConnector\Domain\Model\Connection;
use Brotkrueml\JobRouterConnector\RestClient\RestClientFactory;
use Brotkrueml\JobRouterData\Domain\Model\Table;
use Brotkrueml\JobRouterData\Domain\Repository\TableRepository;
use Brotkrueml\JobRouterData\Exception\ConnectionNotAvailableException;
use Brotkrueml\JobRouterData\Exception\DatasetNotAvailableException;
use Brotkrueml\JobRouterData\Exception\DatasetsNotAvailableException;
use Brotkrueml\JobRouterData\Exception\TableNotAvailableException;

class JobDataRepository
{
    protected const RESOURCE_TEMPLATE_DELETE = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_GET_ALL = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_GET_JRID = 'application/jobdata/tables/%s/datasets/%d';
    protected const RESOURCE_TEMPLATE_POST = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_PUT = 'application/jobdata/tables/%s/datasets/%d';

    /**
     * @var Table
     */
    protected $table;

    private ?ClientInterface $client = null;

    public function __construct(
        private readonly RestClientFactory $restClientFactory,
        private readonly TableRepository $tableRepository,
        private readonly string $tableHandle
    ) {
    }

    protected function getClient(): ClientInterface
    {
        if (! $this->client instanceof ClientInterface) {
            $table = $this->tableRepository->findOneByHandle($this->tableHandle);
            if (! $table instanceof Table) {
                throw new TableNotAvailableException(
                    \sprintf('Table with handle "%s" is not available!', $this->tableHandle),
                    1595951023
                );
            }

            $this->table = $table;
            $connection = $this->table->getConnection();
            if (! $connection instanceof Connection) {
                throw new ConnectionNotAvailableException(
                    \sprintf('Connection for table with handle "%s" is not available!', $this->tableHandle),
                    1595951024
                );
            }

            $this->client = $this->restClientFactory->create($connection);
        }

        return $this->client;
    }

    /**
     * @param array<string, string|int|float|bool|null> $dataset
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function add(array $dataset): array
    {
        $response = $this->getClient()->request(
            'POST',
            \sprintf(self::RESOURCE_TEMPLATE_POST, $this->table->getTableGuid()),
            [
                'dataset' => $dataset,
            ]
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    public function remove(int ...$jrids): void
    {
        $datasets = [];
        foreach ($jrids as $jrid) {
            // @phpstan-ignore-next-line
            $datasets[] = [
                'jrid' => $jrid,
            ];
        }

        $this->getClient()->request(
            'DELETE',
            \sprintf(self::RESOURCE_TEMPLATE_DELETE, $this->table->getTableGuid()),
            [
                'datasets' => $datasets,
            ]
        );
    }

    /**
     * @param array<string, string|int|float|bool|null> $dataset
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function update(int $jrid, array $dataset): array
    {
        $response = $this->getClient()->request(
            'PUT',
            \sprintf(self::RESOURCE_TEMPLATE_PUT, $this->table->getTableGuid(), $jrid),
            [
                'dataset' => $dataset,
            ]
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    /**
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function findAll(): array
    {
        $response = $this->getClient()->request(
            'GET',
            \sprintf(self::RESOURCE_TEMPLATE_GET_ALL, $this->table->getTableGuid())
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    /**
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function findByJrid(int $jrid): array
    {
        try {
            $response = $this->getClient()->request(
                'GET',
                \sprintf(self::RESOURCE_TEMPLATE_GET_JRID, $this->table->getTableGuid(), $jrid)
            );
        } catch (ExceptionInterface $e) {
            throw new DatasetNotAvailableException(
                \sprintf('Dataset with jrid "%d" is not available', $jrid),
                1613047932,
                $e
            );
        }

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    /**
     * @return mixed[]
     */
    protected function buildDatasetsArrayFromJson(string $json): array
    {
        try {
            $decodedJson = \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decodedJson = [];
        }
        if (! \array_key_exists('datasets', $decodedJson)) {
            throw new DatasetsNotAvailableException(
                \sprintf('Key "datasets" is not available in response, given: %s', $json),
                1595954069
            );
        }

        return $decodedJson['datasets'];
    }
}
