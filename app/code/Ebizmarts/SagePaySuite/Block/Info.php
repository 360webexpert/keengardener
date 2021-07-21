<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block;

/**
 * Sage Pay generic payment info block
 * Uses default template
 */
class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @param null $transport
     * @return mixed
     */
    // @codingStandardsIgnoreStart
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();

        $info = [];
        if ($payment->getCcExpMonth()) {
            $info["Credit Card Expiration"] = $payment->getCcExpMonth() . "/" . $payment->getCcExpYear();
        }

        return $transport->addData($info);
    }
    // @codingStandardsIgnoreEnd
}
