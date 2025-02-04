<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Transfer;

use Brotkrueml\JobRouterData\Domain\Repository\TransferRepository;
use Brotkrueml\JobRouterData\Exception\DeleteException;
use Psr\Log\LoggerInterface;

/**
 * @internal Only to be used within the jobrouter_data extension, not part of the public API
 */
class Deleter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TransferRepository $transferRepository,
    ) {
    }

    public function run(int $ageInDays): \Doctrine\DBAL\Result|int
    {
        $this->logger->info('Starting clean up of old transfers');

        $maximumTimestampForDeletion = \time() - $ageInDays * 86400;

        $this->logger->debug('Maximum timestamp for deletion: ' . $maximumTimestampForDeletion);

        try {
            $affectedRows = $this->transferRepository->deleteOldSuccessfulTransfers($maximumTimestampForDeletion);
        } catch (\Exception $e) {
            $message = 'Error on clean up of old transfers: ' . $e->getMessage();
            $this->logger->error($message);
            throw new DeleteException($message, 1582139672, $e);
        }

        if ($affectedRows === 0) {
            $this->logger->info('No affected rows');
        } else {
            $this->logger->notice('Affected rows: ' . $affectedRows);
        }

        return $affectedRows;
    }
}
