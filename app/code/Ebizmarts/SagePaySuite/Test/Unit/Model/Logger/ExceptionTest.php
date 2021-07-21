<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Logger;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Exception
     */
    private $exceptionLoggerModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exceptionLoggerModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Logger\Exception',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testIsHandling()
    {
        $this->assertEquals(
            true,
            $this->exceptionLoggerModel->isHandling(
                ['level'=>\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_EXCEPTION]
            )
        );
    }
}
