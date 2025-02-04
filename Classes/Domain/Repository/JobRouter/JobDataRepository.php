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
use Brotkrueml\JobRouterConnector\Domain\Repository\ConnectionRepository;
use Brotkrueml\JobRouterConnector\Exception\ConnectionNotFoundException;
use Brotkrueml\JobRouterConnector\RestClient\RestClientFactoryInterface;
use Brotkrueml\JobRouterData\Domain\Entity\Table;
use Brotkrueml\JobRouterData\Domain\Repository\TableRepository;
use Brotkrueml\JobRouterData\Exception\ConnectionNotAvailableException;
use Brotkrueml\JobRouterData\Exception\DatasetNotAvailableException;
use Brotkrueml\JobRouterData\Exception\DatasetsNotAvailableException;
use Brotkrueml\JobRouterData\Exception\TableNotAvailableException;
use Brotkrueml\JobRouterData\Exception\TableNotFoundException;

class JobDataRepository
{
    protected const RESOURCE_TEMPLATE_DELETE = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_GET_ALL = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_GET_JRID = 'application/jobdata/tables/%s/datasets/%d';
    protected const RESOURCE_TEMPLATE_POST = 'application/jobdata/tables/%s/datasets';
    protected const RESOURCE_TEMPLATE_PUT = 'application/jobdata/tables/%s/datasets/%d';

    /**
     * @var array<string, ClientInterface>
     */
    private array $clients = [];
    /**
     * @var array<string, Table>
     */
    private array $tables = [];

    public function __construct(
        private readonly ConnectionRepository $connectionRepository,
        private readonly RestClientFactoryInterface $restClientFactory,
        private readonly TableRepository $tableRepository,
    ) {
    }

    protected function getClient(string $tableHandle): ClientInterface
    {
        if (! isset($this->clients[$tableHandle])) {
            try {
                $table = $this->tableRepository->findByHandle($tableHandle);
            } catch (TableNotFoundException) {
                throw new TableNotAvailableException(
                    \sprintf('Table with handle "%s" is not available!', $tableHandle),
                    1595951023,
                );
            }

            try {
                $connection = $this->connectionRepository->findByUid($table->connectionUid);
            } catch (ConnectionNotFoundException) {
                throw new ConnectionNotAvailableException(
                    \sprintf('Connection for table with handle "%s" is not available!', $tableHandle),
                    1595951024,
                );
            }

            $this->tables[$tableHandle] = $table;
            $this->clients[$tableHandle] = $this->restClientFactory->create($connection);
        }

        return $this->clients[$tableHandle];
    }

    /**
     * @param array<string, string|int|float|bool|null> $dataset
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function add(string $tableHandle, array $dataset): array
    {
        $response = $this->getClient($tableHandle)->request(
            'POST',
            \sprintf(self::RESOURCE_TEMPLATE_POST, $this->tables[$tableHandle]->tableGuid),
            [
                'dataset' => $dataset,
            ],
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    public function remove(string $tableHandle, int ...$jrids): void
    {
        $datasets = [];
        foreach ($jrids as $jrid) {
            $datasets[] = [
                'jrid' => $jrid,
            ];
        }

        $this->getClient($tableHandle)->request(
            'DELETE',
            \sprintf(self::RESOURCE_TEMPLATE_DELETE, $this->tables[$tableHandle]->tableGuid),
            [
                'datasets' => $datasets,
            ],
        );
    }

    /**
     * @param array<string, string|int|float|bool|null> $dataset
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function update(string $tableHandle, int $jrid, array $dataset): array
    {
        $response = $this->getClient($tableHandle)->request(
            'PUT',
            \sprintf(self::RESOURCE_TEMPLATE_PUT, $this->tables[$tableHandle]->tableGuid, $jrid),
            [
                'dataset' => $dataset,
            ],
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    /**
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function findAll(string $tableHandle): array
    {
        $response = $this->getClient($tableHandle)->request(
            'GET',
            \sprintf(self::RESOURCE_TEMPLATE_GET_ALL, $this->tables[$tableHandle]->tableGuid),
        );

        return $this->buildDatasetsArrayFromJson($response->getBody()->getContents());
    }

    /**
     * @return list<array<string, string|int|float|bool|null>>
     */
    public function findByJrid(string $tableHandle, int $jrid): array
    {
        try {
            $response = $this->getClient($tableHandle)->request(
                'GET',
                \sprintf(self::RESOURCE_TEMPLATE_GET_JRID, $this->tables[$tableHandle]->tableGuid, $jrid),
            );
        } catch (ExceptionInterface $e) {
            throw new DatasetNotAvailableException(
                \sprintf('Dataset with jrid "%d" is not available', $jrid),
                1613047932,
                $e,
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
            $decodedJson = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decodedJson = [];
        }
        if (! \array_key_exists('datasets', $decodedJson)) {
            throw new DatasetsNotAvailableException(
                \sprintf('Key "datasets" is not available in response, given: %s', $json),
                1595954069,
            );
        }

        return $decodedJson['datasets'];
    }
}
