<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

use Ebizmarts\SagePaySuite\Model\Config\Source\PiChallengeWindowSize;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PiChallengeWindowSizeTest extends \PHPUnit\Framework\TestCase
{
    private const CHALLENGE_WINDOW_SIZE_OPTIONS_COUNT = 5;

    public function testToOptionArray() : void
    {
        $objectManagerHelper = new ObjectManager($this);
        $challengeWindowSize = $objectManagerHelper->getObject(PiChallengeWindowSize::class);

        $availableOptions = $challengeWindowSize->toOptionArray();

        $this->assertEquals(
            [
                'value' => 'Small',
                'label' => __('250px x 400px'),
            ],
            $availableOptions[0]
        );
        $this->assertEquals(
            [
                'value' => 'Medium',
                'label' => __('390px x 400px'),
            ],
            $availableOptions[1]
        );
        $this->assertEquals(
            [
                'value' => 'Large',
                'label' => __('500px x 600px'),
            ],
            $availableOptions[2]
        );
        $this->assertEquals(
            [
                'value' => 'ExtraLarge',
                'label' => __('600px x 400px'),
            ],
            $availableOptions[3]
        );
        $this->assertEquals(
            [
                'value' => 'FullScreen',
                'label' => __('Fullscreen'),
            ],
            $availableOptions[4]
        );

        $this->assertCount(self::CHALLENGE_WINDOW_SIZE_OPTIONS_COUNT, $availableOptions);
    }
}
