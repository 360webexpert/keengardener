<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config to handle all sagepay integrations configs
 */
class Config
{
    /**
     * SagePay VPS protocol
     */
    const VPS_PROTOCOL      = '3.00';
    const VPS_PROTOCOL_FOUR = '4.00';

    /**
     * SagePaySuite Integration codes
     */
    const METHOD_FORM = 'sagepaysuiteform';
    const METHOD_PI = 'sagepaysuitepi';
    const METHOD_SERVER = 'sagepaysuiteserver';
    const METHOD_PAYPAL = 'sagepaysuitepaypal';
    const METHOD_REPEAT = 'sagepaysuiterepeat';

    /**
     * Actions
     */
    const ACTION_PAYMENT         = 'PAYMENT';
    const ACTION_PAYMENT_PI      = 'Payment';
    const ACTION_DEFER           = 'DEFERRED';
    const ACTION_DEFER_PI        = 'Deferred';
    const ACTION_AUTHENTICATE    = 'AUTHENTICATE';
    const ACTION_VOID            = 'VOID';
    const ACTION_REFUND          = 'REFUND';
    const ACTION_RELEASE         = 'RELEASE';
    const ACTION_REPEAT          = 'REPEAT';
    const ACTION_REPEAT_PI       = 'Repeat';
    const ACTION_REPEAT_DEFERRED = 'REPEATDEFERRED';
    const ACTION_AUTHORISE       = 'AUTHORISE';
    const ACTION_POST            = 'post';
    const ACTION_ABORT           = 'ABORT';
    const ACTION_CANCEL          = 'CANCEL';

    /**
     * SagePay MODES
     */
    const MODE_TEST = 'test';
    const MODE_LIVE = 'live';
    const MODE_DEVELOPMENT = 'development';

    /**
     * 3D secure MODES
     */
    const MODE_3D_DEFAULT = 'UseMSPSetting'; // '0' for old integrations
    const MODE_3D_FORCE = 'Force'; // '1' for old integrations
    const MODE_3D_DISABLE = 'Disable'; // '2' for old integrations
    const MODE_3D_IGNORE = 'ForceIgnoringRules'; // '3' for old integrations

    /**
     * AvsCvc MODES
     */
    const MODE_AVSCVC_DEFAULT = 'UseMSPSetting'; // '0' for old integrations
    const MODE_AVSCVC_FORCE = 'Force'; // '1' for old integrations
    const MODE_AVSCVC_DISABLE = 'Disable'; // '2' for old integrations
    const MODE_AVSCVC_IGNORE = 'ForceIgnoringRules'; // '3' for old integrations

    /**
     * FORM Send Email MODES
     */
    const MODE_FORM_SEND_EMAIL_NONE = 0; //  Do not send either customer or vendor emails
    const MODE_FORM_SEND_EMAIL_BOTH = 1; // Send customer and vendor emails if addresses are provided
    const MODE_FORM_SEND_EMAIL_ONLY_VENDOR = 2; //  Send vendor email but NOT the customer email

    /**
     * Currency settings
     */
    const CURRENCY_BASE     = "base_currency";
    const CURRENCY_STORE    = "store_currency";
    const CURRENCY_SWITCHER = "switcher_currency";

    /**
     * SagePay URLs
     */
    const URL_FORM_REDIRECT_LIVE         = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
    const URL_FORM_REDIRECT_TEST         = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
    const URL_PI_API_LIVE                = 'https://pi-live.sagepay.com/api/v1/';
    const URL_PI_API_DEV                 = 'http://pi-test.sagepay.com/api/v1/';
    const URL_PI_API_TEST                = 'https://pi-test.sagepay.com/api/v1/';
    const URL_REPORTING_API_TEST         = 'https://test.sagepay.com/access/access.htm';
    const URL_REPORTING_API_LIVE         = 'https://live.sagepay.com/access/access.htm';
    const URL_REPORTING_API_DEV          = 'http://test.sagepay.com/access/access.htm';
    const URL_SHARED_VOID_TEST           = 'https://test.sagepay.com/gateway/service/void.vsp';
    const URL_SHARED_VOID_LIVE           = 'https://live.sagepay.com/gateway/service/void.vsp';
    const URL_SHARED_CANCEL_TEST         = 'https://test.sagepay.com/gateway/service/cancel.vsp';
    const URL_SHARED_CANCEL_LIVE         = 'https://live.sagepay.com/gateway/service/cancel.vsp';
    const URL_SHARED_REFUND_TEST         = 'https://test.sagepay.com/gateway/service/refund.vsp';
    const URL_SHARED_REFUND_LIVE         = 'https://live.sagepay.com/gateway/service/refund.vsp';
    const URL_SHARED_RELEASE_TEST        = 'https://test.sagepay.com/gateway/service/release.vsp';
    const URL_SHARED_RELEASE_LIVE        = 'https://live.sagepay.com/gateway/service/release.vsp';
    const URL_SHARED_AUTHORISE_TEST      = 'https://test.sagepay.com/gateway/service/authorise.vsp';
    const URL_SHARED_AUTHORISE_LIVE      = 'https://live.sagepay.com/gateway/service/authorise.vsp';
    const URL_SHARED_REPEATDEFERRED_TEST = 'https://test.sagepay.com/gateway/service/repeat.vsp';
    const URL_SHARED_REPEATDEFERRED_LIVE = 'https://live.sagepay.com/gateway/service/repeat.vsp';
    const URL_SHARED_REPEAT_TEST         = 'https://test.sagepay.com/gateway/service/repeat.vsp';
    const URL_SHARED_REPEAT_LIVE         = 'https://live.sagepay.com/gateway/service/repeat.vsp';
    const URL_SHARED_ABORT_TEST          = 'https://test.sagepay.com/gateway/service/abort.vsp';
    const URL_SHARED_ABORT_LIVE          = 'https://live.sagepay.com/gateway/service/abort.vsp';
    const URL_SERVER_POST_TEST           = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
    const URL_SERVER_POST_DEV            = 'http://test.sagepay.com/gateway/service/vspserver-register.vsp';
    const URL_SERVER_POST_LIVE           = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
    const URL_DIRECT_POST_TEST           = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
    const URL_DIRECT_POST_LIVE           = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
    const URL_PAYPAL_COMPLETION_TEST     = 'https://test.sagepay.com/gateway/service/complete.vsp';
    const URL_PAYPAL_COMPLETION_LIVE     = 'https://live.sagepay.com/gateway/service/complete.vsp';
    const URL_TOKEN_POST_REMOVE_LIVE     = 'https://live.sagepay.com/gateway/service/removetoken.vsp';
    const URL_TOKEN_POST_REMOVE_TEST     = 'https://test.sagepay.com/gateway/service/removetoken.vsp';

    /**
     * SagePay Status Codes
     */
    const SUCCESS_STATUS         = '0000';
    const AUTH3D_REQUIRED_STATUS = '2007';
    const AUTH3D_V2_REQUIRED_STATUS = '2021';

    /**
     * SagePay Third Man Score Statuses
     */
    const T3STATUS_NORESULT = 'NORESULT';
    const T3STATUS_OK       = 'OK';
    const T3STATUS_HOLD     = 'HOLD';
    const T3STATUS_REJECT   = 'REJECT';

    /**
     * SagePay Response Statuses
     */
    const OK_STATUS             = 'OK';
    const PENDING_STATUS        = 'PENDING';
    const REGISTERED_STATUS     = 'REGISTERED';
    const DUPLICATED_STATUS     = 'DUPLICATED';
    const AUTHENTICATED_STATUS  = 'AUTHENTICATED';

    /**
     * SagePay ReD Score Statuses
     */
    const REDSTATUS_ACCEPT     = 'ACCEPT';
    const REDSTATUS_DENY       = 'DENY';
    const REDSTATUS_CHALLENGE  = 'CHALLENGE';
    const REDSTATUS_NOTCHECKED = 'NOTCHECKED';

    /**
     * Basket Formats
     */
    const BASKETFORMAT_SAGE50   = 'Sage50';
    const BASKETFORMAT_XML      = 'xml';
    const BASKETFORMAT_DISABLED = 'Disabled';

    /**
     * Current payment method code
     *
     * @var string
     */
    private $methodCode;

    /**
     * Current store id
     *
     * @var int
     */
    private $configurationScopeId;

    /** @var string */
    private $configurationScope;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Logger $suiteLogger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Logger $suiteLogger
    ) {

        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->suiteLogger  = $suiteLogger;

        $this->configurationScopeId = null;
        $this->configurationScope   = ScopeInterface::SCOPE_STORE;
    }

    /**
     * @param $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
        return $this;
    }

    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->methodCode;
    }

    /**
     * Returns payment configuration value
     *
     * @param string $key
     * @param null $configurationScopeId
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($key, $configurationScopeId = null)
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($configurationScopeId);

        $path = $this->getSpecificConfigPath($key);

        return $this->scopeConfig->getValue($path, $this->configurationScope, $resolvedConfigurationScopeId);
    }

    public function getGlobalValue($key, $configurationScopeId = null)
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($configurationScopeId);

        $path = $this->getGlobalConfigPath($key);

        return $this->scopeConfig->getValue($path, $this->configurationScope, $resolvedConfigurationScopeId);
    }

    public function getAdvancedValue($key)
    {
        $config_value = $this->scopeConfig->getValue(
            $this->getAdvancedConfigPath($key),
            $this->configurationScope,
            $this->configurationScopeId
        );
        return $config_value;
    }

    /**
     * @return int
     */
    private function getCurrentStoreId()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        return $store->getId();
    }

    /**
     * Store ID setter
     *
     * @param int $configurationScopeId
     * @return void
     */
    public function setConfigurationScopeId($configurationScopeId)
    {
        $this->configurationScopeId = (int)$configurationScopeId;
    }

    /**
     * @param string $configurationScope
     */
    public function setConfigurationScope($configurationScope)
    {
        $this->configurationScope = $configurationScope;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     */
    private function getSpecificConfigPath($fieldName)
    {
        return "payment/{$this->methodCode}/{$fieldName}";
    }

    private function getGlobalConfigPath($fieldName)
    {
        return "sagepaysuite/global/{$fieldName}";
    }

    private function getAdvancedConfigPath($fieldName)
    {
        return "sagepaysuite/advanced/{$fieldName}";
    }

    /**
     * Check whether method active in configuration and supported for merchant country or not
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodActive()
    {
        return $this->getValue("active");
    }

    /**
     * Check whether method active for backend transactions.
     *
     */
    public function isMethodActiveMoto()
    {
        return $this->getValue("active_moto");
    }

    public function getVPSProtocol()
    {
        return self::VPS_PROTOCOL;
    }

    public function getSagepayPaymentAction()
    {
        return $this->getValue("payment_action");
    }

    public function getPaymentAction()
    {
        $action = $this->getValue("payment_action");

        $magentoAction = null;

        switch ($action) {
            case self::ACTION_PAYMENT:
            case self::ACTION_REPEAT:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
                break;
            case self::ACTION_DEFER:
            case self::ACTION_AUTHENTICATE:
            case self::ACTION_REPEAT_DEFERRED:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE;
                break;
            default:
                $magentoAction = AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
                break;
        }

        return $magentoAction;
    }

    public function getVendorname()
    {
        return $this->getGlobalValue("vendorname");
    }

    public function getLicense()
    {
        return $this->getGlobalValue("license");
    }

    public function getStoreDomain()
    {
        $resolvedConfigurationScopeId = $this->resolveConfigurationScopeId($this->configurationScopeId);
        return $this->scopeConfig->getValue(
            Store::XML_PATH_SECURE_BASE_URL,
            $this->configurationScope,
            $resolvedConfigurationScopeId
        );
    }

    /**
     * @return null|string
     */
    public function getFormEncryptedPassword()
    {
        return $this->getValue("encrypted_password");
    }

    public function getFormSendEmail()
    {
        return $this->getValue("send_email");
    }

    public function getFormVendorEmail()
    {
        return $this->getValue("vendor_email");
    }

    public function getFormEmailMessage()
    {
        return $this->getValue("email_message");
    }

    public function getMode()
    {
        return $this->getGlobalValue("mode");
    }

    /**
     * @return string 3.00|4.00
     */
    public function getProtocolVersion()
    {
        return $this->getGlobalValue("protocol");
    }

    public function shouldUse3dV2()
    {
        return self::VPS_PROTOCOL_FOUR === $this->getProtocolVersion();
    }

    public function isTokenEnabled()
    {
        return $this->getGlobalValue("token");
    }

    public function getReportingApiUser()
    {
        return $this->getGlobalValue("reporting_user");
    }

    public function getReportingApiPassword()
    {
        return $this->getGlobalValue("reporting_password");
    }

    public function getPIPassword()
    {
        return $this->getValue("password");
    }

    public function getPIKey()
    {
        return $this->getValue("key");
    }

    /**
     * return 3D secure rules setting
     * @param bool $forceDisable
     * @return mixed|string
     */
    public function get3Dsecure($forceDisable = false)
    {
        $config_value = $this->scopeConfig->getValue(
            $this->getAdvancedConfigPath("threedsecure"),
            $this->configurationScope,
            $this->configurationScopeId
        );

        if ($forceDisable) {
            $config_value = self::MODE_3D_DISABLE;
        }

        if ($this->methodCode != self::METHOD_PI) {
            $config_value = $this->getThreeDSecureLegacyIntegrations($config_value);
        }

        return $config_value;
    }

    /**
     * return AVS_CVC rules setting
     * @return string
     */
    public function getAvsCvc()
    {
        $configValue = $this->getAdvancedValue("avscvc");

        if ($this->methodCode != self::METHOD_PI) {
            $configValue = $this->getAvsCvcLegacyIntegrations($configValue);
        }

        return $configValue;
    }

    public function getAutoInvoiceFraudPassed()
    {
        return $this->getAdvancedValue("fraud_autoinvoice");
    }

    public function getNotifyFraudResult()
    {
        return $this->getAdvancedValue("fraud_notify");
    }

    public function getPaypalBillingAgreement()
    {
        return $this->getValue("billing_agreement");
    }

    public function getAllowedCcTypes()
    {
        return $this->getValue("cctypes");
    }

    public function dropInEnabled()
    {
        return (bool)($this->getValue("use_dropin") == 1);
    }

    public function getAreSpecificCountriesAllowed()
    {
        return $this->getValue("allowspecific");
    }

    public function getSpecificCountries()
    {
        return $this->getValue("specificcountry");
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function getQuoteCurrencyCode($quote)
    {
        $storeId = $quote->getStoreId();

        $this->setConfigurationScopeId($storeId);
        $currencyConfig = $this->getCurrencyConfig();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($storeId);

        switch ($currencyConfig) {
            case self::CURRENCY_STORE:
                $currency = $store->getDefaultCurrencyCode();
                break;
            case self::CURRENCY_SWITCHER:
                $currency = $store->getCurrentCurrencyCode();
                break;
            default:
                $currency = $store->getBaseCurrencyCode();
        }

        return $currency;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getQuoteAmount($quote)
    {
        $this->setConfigurationScopeId($quote->getStoreId());
        $currencyConfig = $this->getCurrencyConfig();

        switch ($currencyConfig) {
            case Config::CURRENCY_STORE:
            case Config::CURRENCY_SWITCHER:
                $amount = $quote->getGrandTotal();
                break;
            default:
                $amount = $quote->getBaseGrandTotal();
        }

        return $amount;
    }

    public function getCurrencyConfig()
    {
        return $this->getGlobalValue("currency");
    }

    public function getBasketFormat()
    {
        return $this->getAdvancedValue("basket_format");
    }

    public function isPaypalForceXml()
    {
        return $this->getValue("force_xml");
    }

    public function isGiftAidEnabled()
    {
        return $this->getAdvancedValue("giftaid");
    }

    public function isServerLowProfileEnabled()
    {
        return $this->getValue("profile");
    }

    /**
     * @param $methodCode
     * @return bool
     */
    public function isSagePaySuiteMethod($methodCode)
    {
        return $methodCode == self::METHOD_PAYPAL ||
        $methodCode == self::METHOD_PI ||
        $methodCode == self::METHOD_FORM ||
        $methodCode == self::METHOD_SERVER ||
        $methodCode == self::METHOD_REPEAT;
    }

    /**
     * @param $configValue
     * @return string
     */
    private function getThreeDSecureLegacyIntegrations($configValue)
    {
        //for old integrations
        switch ($configValue) {
            case self::MODE_3D_FORCE:
                $return = '1';
                break;
            case self::MODE_3D_DISABLE:
                $return = '2';
                break;
            case self::MODE_3D_IGNORE:
                $return = '3';
                break;
            default:
                $return = '0';
                break;
        }

        return $return;
    }

    /**
     * @param $action
     * @return null|string
     */
    public function getServiceUrl($action)
    {
        $mode = $this->getMode();

        $constantName = sprintf("self::URL_SHARED_%s_%s", strtoupper($action), strtoupper($mode));

        return constant($constantName);
    }

    /**
     * @param $configValue
     * @return string
     */
    private function getAvsCvcLegacyIntegrations($configValue)
    {
        switch ($configValue) {
            case self::MODE_AVSCVC_FORCE:
                $return = '1';
                break;
            case self::MODE_AVSCVC_DISABLE:
                $return = '2';
                break;
            case self::MODE_AVSCVC_IGNORE:
                $return = '3';
                break;
            default:
                $return = '0';
                break;
        }

        return $return;
    }

    /**
     * @param $configurationScopeId
     * @return int|null
     */
    private function resolveConfigurationScopeId($configurationScopeId)
    {
        if ($configurationScopeId === null) {
            $configurationScopeId = $this->configurationScopeId;
            if ($configurationScopeId === null) {
                $configurationScopeId = $this->getCurrentStoreId();
            }
        }

        return $configurationScopeId;
    }

    public function getInvoiceConfirmationNotification()
    {
        return $this->getAdvancedValue("invoice_confirmation_notification");
    }

    public function getMaxTokenPerCustomer()
    {
        return $this->getAdvancedValue("max_token");
    }

    public function get3dNewWindow()
    {
        return $this->getValue("threed_new_window");
    }
}
