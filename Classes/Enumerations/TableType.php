<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Enumerations;

/**
 * @internal
 */
enum TableType: int
{
    case Simple = 1;
    case CustomTable = 2;
    case OtherUsage = 3;
    case FormFinisher = 4;
}
