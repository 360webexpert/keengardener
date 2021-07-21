<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Ebizmarts\SagePaySuite\Model\Config as Config;
use Magento\Payment\Helper\Data as PaymentHelper;

class Paypal implements ConfigProviderInterface
{
    /**
     * @var string
     */
    private $methodCode = Config::METHOD_PAYPAL;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Form
     */
    private $method;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $suiteHelper;

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
    ) {
    
        $this->method      = $paymentHelper->getMethodInstance($this->methodCode);
        $this->suiteHelper = $suiteHelper;
    }

    public function getConfig()
    {
        if (!$this->method->isAvailable()) {
            return [];
        }

        return [
            'payment' => [
                'ebizmarts_sagepaysuitepaypal' => [
                    'licensed' => $this->suiteHelper->verify(),
                    'mode' => $this->suiteHelper->getSagePayConfig()->getMode()
                ],
            ]
        ];
    }
}
