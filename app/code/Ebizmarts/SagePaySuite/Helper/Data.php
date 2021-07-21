<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const FRONTEND = "frontend";
    const ADMIN = 'adminhtml';

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $sagePaySuiteConfig;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ModuleVersion
     */
    private $moduleVersion;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * Data constructor.
     * @param Context $context
     * @param Config $config
     * @param DateTime $dateTime
     * @param ModuleVersion $moduleVersion
     * @param StoreManagerInterface $storeManager
     * @param State $state
     */
    public function __construct(
        Context $context,
        Config $config,
        DateTime $dateTime,
        ModuleVersion $moduleVersion,
        StoreManagerInterface $storeManager,
        State $state
    ) {
        parent::__construct($context);
        $this->sagePaySuiteConfig = $config;
        $this->dateTime           = $dateTime;
        $this->moduleVersion      = $moduleVersion;
        $this->storeManager       = $storeManager;
        $this->state              = $state;
    }

    /**
     * Get default sagepay config instance
     * @return \Ebizmarts\SagePaySuite\Model\Config
     */
    public function getSagePayConfig()
    {
        return $this->sagePaySuiteConfig;
    }

    /**
     * @param string $order_id
     * @param string $action
     * @return string
     */
    public function generateVendorTxCode($order_id = "", $action = Config::ACTION_PAYMENT)
    {
        $prefix = "";

        switch ($action) {
            case Config::ACTION_REFUND:
                $prefix = "R";
                break;
            case Config::ACTION_AUTHORISE:
                $prefix = "A";
                break;
            case Config::ACTION_REPEAT:
            case Config::ACTION_REPEAT_PI:
            case Config::ACTION_REPEAT_DEFERRED:
                $prefix = "RT";
                break;
        }

        $sanitizedOrderId = $this->sanitizeOrderId($order_id);
        $date = $this->dateTime->gmtDate('Y-m-d-His');
        $time = $this->dateTime->gmtTimestamp();

        return substr($prefix . $sanitizedOrderId . "-" . $date . $time, 0, 40);
    }

    /**
     * Verify license
     * @return bool
     */
    // @codingStandardsIgnoreStart
    public function verify()
    {
        $this->sagePaySuiteConfig->setConfigurationScopeId($this->obtainConfigurationScopeIdFromRequest());
        $this->sagePaySuiteConfig->setConfigurationScope($this->obtainConfigurationScopeCodeFromRequest());

        $versionNumberToCheck = $this->obtainMajorAndMinorVersionFromVersionNumber(
            $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite')
        );
        $localSignature = $this->localSignature(
            $this->extractHostFromCurrentConfigScopeStoreCheckoutUrl(),
            $versionNumberToCheck
        );

        return ($localSignature == $this->sagePaySuiteConfig->getLicense());
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param string $checkoutHostName
     * @param string X.Y $moduleMajorAndMinorVersionNumber
     * @return string
     */
    private function localSignature($checkoutHostName, $moduleMajorAndMinorVersionNumber)
    {
        $md5    = hash("md5", "Ebizmarts_SagePaySuite2" . $moduleMajorAndMinorVersionNumber . $checkoutHostName);
        $key    = hash("sha1", $md5 . "EbizmartsV2");

        return $key;
    }

    /**
     * @param string semver$versionNumber
     * @return string
     */
    public function obtainMajorAndMinorVersionFromVersionNumber($versionNumber)
    {
        $versionArray = explode('.', $versionNumber);

        return $versionArray[0] . "." . $versionArray[1];
    }

    /**
     * @return int
     */
    public function obtainConfigurationScopeIdFromRequest()
    {
        if ($this->getAreaCode() === self::FRONTEND) {
            return $this->getStoreId();
        }
        return $this->obtainAdminConfigurationScopeIdFromRequest();
    }

    /**
     * @return string
     */
    public function obtainConfigurationScopeCodeFromRequest()
    {
        if ($this->getAreaCode() === self::FRONTEND) {
            return $this->storeScopeCode();
        }
        return $this->obtainAdminConfigurationScopeCodeFromRequest();
    }

    /**
     * @return string
     */
    private function extractHostFromCurrentConfigScopeStoreCheckoutUrl()
    {
        $domain = preg_replace(
            ["/^http:\/\//", "/^https:\/\//", "/^www\./", "/\/$/"],
            "",
            $this->sagePaySuiteConfig->getStoreDomain()
        );

        return $domain;
    }

    /**
     * Stripe transaction if from '-capture/-refund/etc' appends
     * @param $transactionId
     * @return mixed
     */
    public function clearTransactionId($transactionId)
    {
        $suffixes = [
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
        ];
        foreach ($suffixes as $suffix) {
            if (strpos($transactionId, $suffix) !== false) {
                $transactionId = str_replace($suffix, '', $transactionId);
            }
        }
        return $transactionId;
    }

    public function removeCurlyBraces($text)
    {
        return str_replace(["{", "}"], "", $text);
    }

    /**
     * @param string $methodCode
     * @return bool
     */
    public function methodCodeIsSagePay($methodCode)
    {
        return $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_FORM
            or $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL
            or $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_REPEAT
            or $methodCode == \Ebizmarts\SagePaySuite\Model\Config::METHOD_SERVER;
    }

    /**
     * @return string
     */
    private function defaultScopeCode()
    {
        return \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @return string
     */
    private function storeScopeCode()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * @return string
     */
    private function websiteScopeCode()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
    }

    /**
     * @param $configurationScope
     * @return bool
     */
    public function isConfigurationScopeStore($configurationScope)
    {
        return $configurationScope == $this->storeScopeCode();
    }

    /**
     * @param $configurationScope
     * @return bool
     */
    public function isConfigurationScopeWebsite($configurationScope)
    {
        return $configurationScope == $this->websiteScopeCode();
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getAreaCode()
    {
        return $this->state->getAreaCode();
    }

    /**
     * @return string
     */
    public function obtainAdminConfigurationScopeCodeFromRequest()
    {
        $configurationScope = $this->defaultScopeCode();

        /** @var $requestObject \Magento\Framework\App\RequestInterface */
        $requestObject = $this->getRequest();

        $storeParameter = $requestObject->getParam('store');
        if ($storeParameter !== null) {
            $configurationScope = $this->storeScopeCode();
        } else {
            $websiteParameter = $requestObject->getParam('website');
            if ($websiteParameter !== null) {
                $configurationScope = $this->websiteScopeCode();
            }
        }

        return $configurationScope;
    }

    /**
     * @return int
     */
    public function obtainAdminConfigurationScopeIdFromRequest()
    {
        $configurationScopeId = $this->getDefaultStoreId();

        /** @var $requestObject \Magento\Framework\App\RequestInterface */
        $requestObject = $this->getRequest();

        $configurationScope = $this->obtainConfigurationScopeCodeFromRequest();
        if ($this->isConfigurationScopeStore($configurationScope)) {
            $configurationScopeId = $requestObject->getParam('store');
        } elseif ($this->isConfigurationScopeWebsite($configurationScope)) {
            $configurationScopeId = $requestObject->getParam('website');
        }

        return $configurationScopeId;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @param $text
     * @return string
     */
    private function sanitizeOrderId($text)
    {
        return preg_replace("/[^a-zA-Z0-9-\-\{\}\_\.]/", "", $text);
    }
}
