<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Tests\Unit\Domain\Entity;

use Brotkrueml\JobRouterData\Domain\Entity\Dataset;
use PHPUnit\Framework\TestCase;

final class DatasetTest extends TestCase
{
    /**
     * @test
     */
    public function fromArray(): void
    {
        $actual = Dataset::fromArray([
            'uid' => '1',
            'table_uid' => '2',
            'jrid' => '3',
            'dataset' => '{"key": "value"}',
        ]);

        self::assertInstanceOf(Dataset::class, $actual);
        self::assertSame(1, $actual->uid);
        self::assertSame(2, $actual->tableUid);
        self::assertSame(3, $actual->jrid);
        self::assertSame([
            'key' => 'value',
        ], $actual->dataset);
    }
}
