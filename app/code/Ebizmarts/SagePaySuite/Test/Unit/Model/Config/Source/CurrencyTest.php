<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config\Source\Currency
     */
    private $currencyModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currencyModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config\Source\Currency',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::CURRENCY_BASE,
                'label' => __('Base Currency'),
            ],
            $this->currencyModel->toOptionArray()[0]
        );
    }
}
