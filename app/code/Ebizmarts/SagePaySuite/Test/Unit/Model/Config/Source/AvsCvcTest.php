<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

class AvsCvcTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config\Source\AvsCvc
     */
    private $avsCvcSecureModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->avsCvcSecureModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config\Source\AvsCvc',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::MODE_AVSCVC_DEFAULT,
                'label' => __('Default: Use default MySagePay settings'),
            ],
            $this->avsCvcSecureModel->toOptionArray()[0]
        );
    }
}
