<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Logger;

class Request extends \Magento\Framework\Logger\Handler\Base
{

    /**
     * File name
     * @var string
     */

    protected $fileName = '/var/log/SagePaySuite/Request.log'; // @codingStandardsIgnoreLine

    public function isHandling(array $record)
    {
        return $record['level'] == \Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST;
    }
}
