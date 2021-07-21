<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\ConfigProvider;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use \Ebizmarts\SagePaySuite\Model\Config as Config;

class PI extends CcGenericConfigProvider
{

    /**
     * @var string
     */
    private $methodCode = Config::METHOD_PI;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\PI
     */
    private $method;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $_suiteHelper;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $_config;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /**
     * PI constructor.
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Ebizmarts\SagePaySuite\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        parent::__construct($ccConfig, $paymentHelper);

        $this->storeManager = $storeManager;

        $store = $this->storeManager->getStore();

        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_suiteHelper = $suiteHelper;

        $config->setMethodCode($this->methodCode);
        $config->setConfigurationScopeId($store->getId());
        $this->_config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->method->isAvailable()) {
            return [];
        }

        return [
            'payment' => [
                'ebizmarts_sagepaysuitepi' => [
                    'licensed' => $this->_suiteHelper->verify(),
                    'mode'     => $this->_config->getMode(),
                    'sca'      => $this->_config->shouldUse3dV2(),
                    'dropin'   => $this->_config->setMethodCode($this->methodCode)->dropInEnabled(),
                    'newWindow'=> $this->_config->get3dNewWindow()
                ]
            ]
        ];
    }
}
