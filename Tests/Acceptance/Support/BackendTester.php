<?php

declare(strict_types=1);

/*
 * This file is part of the "jobrouter_data" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\JobRouterData\Tests\Acceptance\Support;

use Brotkrueml\JobRouterData\Tests\Acceptance\Support\_generated\BackendTesterActions;
use Brotkrueml\JobRouterData\Tests\Acceptance\Support\Extension\DataActions;
use Codeception\Util\Locator;
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

/**
 * Default backend admin or editor actor in the backend
 */
class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;
    use DataActions;

    public function loginAs(string $username): void
    {
        $I = $this;
        if ($I->loadSessionSnapshot($username . 'Login')) {
            $I->amOnPage('/typo3/index.php');
        } else {
            $I->amOnPage('/typo3/index.php');
            $I->waitForElement('body[data-typo3-login-ready]');
            // logging in
            $I->amOnPage('/typo3/index.php');
            $I->submitForm('#typo3-login-form', [
                'username' => $username,
                'p_field' => 'password',
            ]);
            $I->saveSessionSnapshot($username . 'Login');
        }
        $I->switchToIFrame('list_frame');
        $I->waitForElement(Locator::firstElement('div.module'));
        $I->switchToIFrame();
    }
}
