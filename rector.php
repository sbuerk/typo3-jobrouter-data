<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $config): void {
    $config->phpVersion(PhpVersion::PHP_74);

    $config->sets([
        LevelSetList::UP_TO_PHP_74,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_EXCEPTION,
        PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD,
        PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER
    ]);

    $config->autoloadPaths([
        __DIR__ . '/.Build/vendor/autoload.php',
    ]);
    $config->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/Tests',
    ]);
    $config->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
        ''
    ]);
    $config->skip([
        __DIR__ . '/Tests/Acceptance/*',
        AddLiteralSeparatorToNumberRector::class,
        DateTimeToDateTimeInterfaceRector::class => [
            __DIR__ . '/Classes/Domain/Model/Table.php',
            __DIR__ . '/Classes/Domain/Model/Transfer.php',
        ],
        RemoveUnusedPromotedPropertyRector::class, // Skip until compatibility with PHP >= 8.0
        ReturnTypeDeclarationRector::class => [
            __DIR__ . '/Classes/Domain/Repository/TableRepository.php',
            __DIR__ . '/Classes/Domain/Repository/TransferRepository.php',
        ],
    ]);
};
