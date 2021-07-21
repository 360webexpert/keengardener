<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Logger;

class CronTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Cron
     */
    private $cronLoggerModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cronLoggerModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Logger\Cron',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testIsHandling()
    {
        $this->assertEquals(
            true,
            $this->cronLoggerModel->isHandling(['level'=>\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_CRON])
        );
    }
}
