<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Logger;

use Magento\Framework\Logger\Handler\Base;

class Cron extends Base
{

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/SagePaySuite/Cron.log'; // @codingStandardsIgnoreLine

    public function isHandling(array $record)
    {
        return $record['level'] == Logger::LOG_CRON;
    }
}
