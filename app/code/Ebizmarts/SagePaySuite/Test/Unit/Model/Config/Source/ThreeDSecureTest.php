<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

class ThreeDSecureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config\Source\ThreeDSecure
     */
    private $threedSecureModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->threedSecureModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config\Source\ThreeDSecure',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_3D_DEFAULT,
                'label' => __('Default: Use default MySagePay settings'),
            ],
            $this->threedSecureModel->toOptionArray()[0]
        );
    }
}
