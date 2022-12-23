<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Domain\Repository\QueryBuilder;

use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * @internal
 */
class TransferRepository
{
    private const TABLE_NAME = 'tx_jobrouterdata_domain_model_transfer';

    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @return mixed[]
     */
    public function countGroupByTransmitSuccess(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        return $queryBuilder
            ->select('transmit_success')
            ->addSelectLiteral('COUNT(*) AS ' . $queryBuilder->quoteIdentifier('count'))
            ->from(self::TABLE_NAME)
            ->groupBy('transmit_success')
            ->execute()
            ->fetchAll();
    }

    public function countTransmitFailed(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        $whereExpressions = [
            $queryBuilder->expr()->eq(
                'transmit_success',
                $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->gt(
                'transmit_date',
                $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
            ),
        ];

        $count = $queryBuilder
            ->count('*')
            ->from(self::TABLE_NAME)
            ->where(...$whereExpressions)
            ->execute()
            ->fetchColumn();

        if ($count === false) {
            return 0;
        }

        return $count;
    }

    public function deleteOldSuccessfulTransfers(int $maximumTimestampForDeletion): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        return $queryBuilder
            ->delete(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'transmit_success',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->lt(
                    'crdate',
                    $queryBuilder->createNamedParameter($maximumTimestampForDeletion, \PDO::PARAM_INT)
                )
            )
            ->execute();
    }

    public function findFirstCreationDate(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        $quotedCrdate = $queryBuilder->quoteIdentifier('crdate');

        return $queryBuilder
            ->selectLiteral(\sprintf('MIN(%s)', $quotedCrdate))
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchColumn() ?: 0;
    }
}
