<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\Source\PiPaymentAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PiPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        $objectManagerHelper = new ObjectManager($this);
        $paymentActionModel = $objectManagerHelper->getObject(PiPaymentAction::class, []);

        $availableOptions = $paymentActionModel->toOptionArray();

        $this->assertEquals(
            [
                'value' => Config::ACTION_PAYMENT_PI,
                'label' => __('Payment - Authorize and Capture'),
            ],
            $availableOptions[0]
        );

        $this->assertEquals(
            [
                'value' => Config::ACTION_DEFER_PI,
                'label' => __('Defer - Authorize Only'),
            ],
            $availableOptions[1]
        );
        $this->assertCount(2, $availableOptions);
    }
}
