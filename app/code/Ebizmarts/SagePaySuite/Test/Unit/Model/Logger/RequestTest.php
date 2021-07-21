<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Logger;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Request
     */
    private $requestLoggerModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestLoggerModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Logger\Request',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testIsHandling()
    {
        $this->assertEquals(
            true,
            $this->requestLoggerModel->isHandling(['level'=>\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST])
        );
    }
}
