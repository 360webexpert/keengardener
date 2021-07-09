<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Shipping\Model\Config;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\UrlInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use WeSupply\Toolbox\Model\OrderInfoBuilder;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Logger\Logger;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    /**
     * Platform name
     */
    const WESUPPLY_PLATFORM_TYPE = 'embedded';

    /**
     * WeSupply tracking cms page url
     */
    const WESUPPLY_TRACKING_INFO_URI = 'wesupply/track/shipment';

    /**
     * Array of carrier codes that are excluded from being sent to wesupply validation
     */
    const EXCLUDED_CARRIERS = [
        'flatrate',
        'tablerate',
        'freeshipping'
    ];

    const FILTER_ORDERS_BY_MAP = [
        'OrderShippingCountryCode' => 'wesupply_order_filter_countries'
    ];

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \WeSupply\Toolbox\Api\WeSupplyApiInterface
     */
    protected $weSupplyApi;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipConfig;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var AllowedCountries
     */
    private $allowedCountries;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Data constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SerializerInterface $serializer
     * @param WeSupplyApiInterface $weSupplyApi
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $shipConfig
     * @param CatalogSession $catalogSession
     * @param CustomerSession $customerSession
     * @param CountryFactory $countryFactory
     * @param UrlInterface $urlInterface
     * @param RemoteAddress $remoteAddress
     * @param Http $request
     * @param ThemeProviderInterface $themeProvider
     * @param AllowedCountries $allowedCountries
     * @param Logger $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SerializerInterface $serializer,
        WeSupplyApiInterface $weSupplyApi,
        Context $context,
        StoreManagerInterface $storeManager,
        Config $shipConfig,
        CatalogSession $catalogSession,
        CustomerSession $customerSession,
        CountryFactory $countryFactory,
        UrlInterface $urlInterface,
        RemoteAddress $remoteAddress,
        Http $request,
        ThemeProviderInterface $themeProvider,
        AllowedCountries $allowedCountries,
        Logger $logger
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->weSupplyApi = $weSupplyApi;
        $this->shipConfig = $shipConfig;
        $this->catalogSession = $catalogSession;
        $this->customerSession = $customerSession;
        $this->allowedCountries = $allowedCountries;
        $this->countryFactory = $countryFactory;
        $this->_urlInterface = $urlInterface;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
        $this->themeProvider = $themeProvider;
        $this->logger = $logger;
     }

    /**
     * @return mixed
     */
    public function getWeSupplyEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/access_key', ScopeInterface::SCOPE_STORE);
    }

    public function getGuidByScope()
    {
        $params = $this->request->getParams();
        if (isset($params['store'])) {
            return $this->scopeConfig->getValue('wesupply_api/integration/access_key', ScopeInterface::SCOPE_STORE, $params['store']);
        }
        if (isset($params['website'])) {
            return $this->scopeConfig->getValue('wesupply_api/integration/access_key', ScopeInterface::SCOPE_WEBSITE, $params['website']);
        }

        return $this->scopeConfig->getValue('wesupply_api/integration/access_key', 'default');
    }

    /**
     * @param int $copeId
     * @return bool|string
     */
    public function getApiEndpointByScope($copeId = 0)
    {
        try {
            return $this->getBaseUrlByScopeConfigView($this->getScopeConfigView($copeId)) . 'wesupply';
        } catch (NoSuchEntityException $e) {
            $this->logger->addError($e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->addError($e->getMessage());
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getClientName()
    {
        // same as wesupply subdomain
        return $this->getWeSupplySubDomain();
    }

    /**
     * @return bool|mixed|null
     */
    public function getClientNameByScope()
    {
        try {
            return $this->getWeSupplySubDomainByScope($this->getScopeConfigView()) ?? null;
        } catch (NoSuchEntityException $e) {
            $this->logger->addError($e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->addError($e->getMessage());
        }

        return false;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        //return $this->scopeConfig->getValue('wesupply_api/massupdate/batch_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return 0;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_protocol', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return self::WESUPPLY_PLATFORM_TYPE;
    }

    /**
     * @return mixed
     */
    public function getWeSupplyDomain()
    {
        if (isset($_SERVER['SERVER_TYPE']) && $_SERVER['SERVER_TYPE'] == 'localdev') {
            return $_SERVER['LOCAL_DOMAIN'];
        }

        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_domain', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getWeSupplySubDomain()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_subdomain', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getWeSupplySubDomainByScope(
        $scopeConfig = [
            'scope_type' => ScopeInterface::SCOPE_STORE,
            'scope_code' => null
        ]
    )
    {
        return $this->scopeConfig->getValue(
            'wesupply_api/integration/wesupply_subdomain',
            $scopeConfig['scope_type'],
            $scopeConfig['scope_code']
        );
    }

    /**
     * @return mixed
     */
    public function getEnabledNotification()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/checkout_page_notification', ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationDesign()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/design_notification', ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationAlignment()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/design_notification_alingment', ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getNotificationBoxType()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_4/notification_type', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getEnableWeSupplyOrderView()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getEnableWeSupplyAdminOrder()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_admin_order_enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getEnableWeSupplyAdminReturns()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_admin_returns_enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getWeSupplyApiClientId()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_client_id', ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return mixed
     */
    public function getWeSupplyApiClientSecret()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_client_secret', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getConnectionStatusByScope()
    {
        // need more store details
        $params = $this->request->getParams();
        if (isset($params['store'])) {
            return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_connection_status', ScopeInterface::SCOPE_STORE, $params['store']);
        }
        if (isset($params['website'])) {
            return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_connection_status', ScopeInterface::SCOPE_WEBSITE, $params['website']);
        }

        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_connection_status', 'default');
    }

    /**
     * @return mixed
     */
    public function getConnectionStatus()
    {
        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_connection_status', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getWeSupplyOrderViewEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsHeaderLinkEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/enable_delivery_estimations_header_link', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/enable_delivery_estimations', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsRange()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_range', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsFormat()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_format', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimationsOrderWithin()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_order_within', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getDisplaySpinner()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_5/estimation_display_spinner', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|bool
     */
    public function getEstimationsDefaultCarrierAndMethod()
    {
        $defaultCarrier = $this->scopeConfig->getValue('wesupply_api/step_5/estimation_default_carrier', ScopeInterface::SCOPE_STORE);
        if($defaultCarrier == '0'){
            return FALSE;
        }

        try {
            $searchedMethod = strtolower($defaultCarrier);
            $defaultMethod = $this->scopeConfig->getValue('wesupply_api/step_5/estimation_carrier_methods_' . $searchedMethod, ScopeInterface::SCOPE_STORE);


            return ['carrier' => $defaultCarrier , 'method'=> $defaultMethod];
        }catch (\Exception $e)
        {
            return FALSE;
        }
    }

    /**
     * @return mixed
     */
    public function orderViewModalEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_order_view_iframe', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function trackingInfoIframeEnabled()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_3/wesupply_tracking_info_iframe', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orders
     * @return string
     */
    public function externalOrderIdString($orders)
    {
        $arrayOrders = $orders->toArray();

        $externalOrderIdString = implode(',', array_map(function($singleOrderArray) {
            return $singleOrderArray['increment_id'];
        }, $arrayOrders['items']));

        return $externalOrderIdString;
    }

    /**
     * @param $orders
     * @return string
     */
    public function internalOrderIdString($orders)
    {
        $arrayOrders = $orders->toArray();

        $externalOrderIdString = implode(',', array_map(function($singleOrderArray) {
            return OrderInfoBuilder::PREFIX.$singleOrderArray['entity_id'];
        }, $arrayOrders['items']));

        return $externalOrderIdString;
    }

    /**
     * Maps the Wesupply Api Response containing links to each order, to an internal array
     *
     * @param $orders
     * @param bool $ignoreEmailConfirmation
     * @return mixed
     */
    public function getGenerateOrderMap($orders, $ignoreEmailConfirmation = false)
    {
        $orderIds = $this->externalOrderIdString($orders);
        try{
            $this->weSupplyApi->setProtocol($this->getProtocol());
            $this->weSupplyApi->setApiPath($this->getWesupplyApiFullDomain());
            $this->weSupplyApi->setApiClientId($this->getWeSupplyApiClientId());
            $this->weSupplyApi->setApiClientSecret($this->getWeSupplyApiClientSecret());

            $result = $this->weSupplyApi->weSupplyInterogation($orderIds, $ignoreEmailConfirmation);
        }catch(\Exception $e){
            $this->logger->error("Error on WeSupply getGenerateOrderMap: " . $e->getMessage());
        }

        return $result ?? '';
    }

    /**
     * @param $string
     * @return float|int
     */
    public function strbits($string)
    {
        return (strlen($string)*8);
    }

    /**
     * @param $bytes
     * @return string
     */
    public function formatSizeUnits($bytes)
    {

        /**
         * transforming bytes in MB
         */
        if ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2);
        }
        else
        {
            return 0;
        }


        return $bytes;
    }

    /**
     * gets an array of al available shipping methods mapped to wesupply naming conventions
     * @return array
     */
    public function getMappedShippingMethods(){

        try {
            $activeCarriers = $this->shipConfig->getActiveCarriers();
            $methods = array();
            foreach ($activeCarriers as $carrierCode => $carrierModel) {

                if(in_array($carrierCode, self::EXCLUDED_CARRIERS)){
                    continue;
                }

                if(isset(WeSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode])){
                    $carrierCode = WeSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode];
                    $methods[] = $carrierCode;
                }
            }

            return $methods;

        }catch(\Exception $e){
            $this->logger->error("Error on WeSupply getMappedShippingMethods: " . $e->getMessage());
            return [];
        }
    }

    /**
     * returns mapped ups xml carrier code value
     * @param $magentoUpsCarrierCode
     * @return string
     */
    public function getMappedUPSXmlMappings($magentoUpsCarrierCode)
    {
        if(isset(WeSupplyMappings::UPS_XML_MAPPINGS[$magentoUpsCarrierCode])){
            return WeSupplyMappings::UPS_XML_MAPPINGS[$magentoUpsCarrierCode];
        }

        return '';
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function getCountryname($countryCode)
    {
        try {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            return $country->getName();
        }catch(\Exception $e)
        {
            return '';
        }
    }

    /**
     * reverts back wesupply quotes to magento format
     * @param $quotes
     * @return array
     */
    public function revertWesupplyQuotesToMag($quotes)
    {
        $flipedCarrierMappings = array_flip(WeSupplyMappings::MAPPED_CARRIER_CODES);
        $mappedQuotes = [];
        foreach($quotes as $carrierKey => $values)
        {
            $magentoCarrierKey = $carrierKey;
            if(isset($flipedCarrierMappings[$carrierKey])){
                $magentoCarrierKey = $flipedCarrierMappings[$carrierKey];
            }
            $mappedQuotes[$magentoCarrierKey] = $values;
        }
        return $mappedQuotes;
    }

    /**
     * sets estimations data into session if session exists, otherwise creates a new session variable
     * @param $estimations
     */
    public function setEstimationsData($estimations)
    {
        $sessionEstimationsData = $this->catalogSession->getEstimationsData();
        /** existing session variable update */
        if ($sessionEstimationsData) {
            $sessionEstimationsArr = $this->serializer->unserialize($sessionEstimationsData);
            if(isset($estimations['zip'])){
                $sessionEstimationsArr[$estimations['zip']] = $estimations;
                $sessionEstimationsArr['default'] = $estimations['zip'];
                $this->catalogSession->setEstimationsData($this->serializer->serialize($sessionEstimationsArr));
            }
          return;
        }

        /**  new session creation */
        if(isset($estimations['zip'])){
            $sessionEstimationsArr[$estimations['zip']] = $estimations;
            $sessionEstimationsArr['default'] = $estimations['zip'];
            $sessionEstimationsArr['created_at'] = time();
            $this->catalogSession->setEstimationsData($this->serializer->serialize($sessionEstimationsArr));
        }
        return;
    }

    /**
     * Generates all printable options for my account order view
     * @param $order
     * @return array
     */
    public function generateAllPrintableOptionsForOrder($order)
    {
        $options = [];
        $options[] = [
            'label' => __('Print...'),
            'url' => '#'
        ];

        if($order->hasInvoices()){
            $options[] = ['label' => 'All Invoices', 'url' => $this->getPrintAllInvoicesUrl($order)];
        }

        if($order->hasShipments()){
            $options[] = ['label' => 'All Shipments', 'url' => $this->getPrintAllShipmentsUrl($order)];
        }

        if($order->hasCreditmemos()){
            $options[] = ['label' => 'All Refunds', 'url' => $this->getPrintAllCreditMemoUrl($order)];
        }

        return $options;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getPrintAllInvoicesUrl($order)
    {
        return $this->_getUrl('sales/order/printInvoice', ['order_id' => $order->getId()]);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrintAllShipmentsUrl($order)
    {
        return $this->_getUrl('sales/order/printShipment', ['order_id' => $order->getId()]);
    }

    /**
     * @param $order
     * @return string
     */
    public function getPrintAllCreditMemoUrl($order)
    {
        return $this->_getUrl('sales/order/printCreditmemo', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getWesupplyFullDomain()
    {
        return $this->getWeSupplySubDomain() !== 'install' ?
            $this->weSupplyHasDomainAlias() ?
                $this->getProtocol() . '://' . $this->getWeSupplyDomain() . '/' :
                $this->getProtocol() . '://' . $this->getWeSupplySubDomain() . '.' . $this->getWeSupplyDomain() . '/'
            : '';
    }

    /**
     * @return string
     */
    public function getWesupplyFullDomainDefault()
    {
        $wsDomainDefault = $this->scopeConfig->getValue(
            'wesupply_api/integration/wesupply_domain_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (isset($_SERVER['SERVER_TYPE']) && $_SERVER['SERVER_TYPE'] == 'localdev') {
            $wsDomainDefault = $_SERVER['LOCAL_DOMAIN_DEFAULT'];
        }

        return $this->getWeSupplySubDomain() !== 'install' ?
            $this->getProtocol() . '://' . $this->getWeSupplySubDomain() . '.' . $wsDomainDefault . '/' : '';
    }

    /**
     * @return mixed
     */
    public function getWesupplyDomainDefault()
    {
        if (isset($_SERVER['SERVER_TYPE']) && $_SERVER['SERVER_TYPE'] == 'localdev') {
            return $_SERVER['LOCAL_DOMAIN_DEFAULT'];
        }

        return $this->scopeConfig->getValue('wesupply_api/integration/wesupply_domain_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getWesupplyApiFullDomain()
    {
        return $this->getWeSupplySubDomain() !== 'install' ?
            $this->weSupplyHasDomainAlias() ?
                $this->getWeSupplyDomain() . '/api/' :
                $this->getWeSupplySubDomain() . '.' . $this->getWeSupplyDomain() . '/api/'
            : '';
    }

    /**
     * @return bool
     */
    public function weSupplyHasDomainAlias()
    {
        return $this->scopeConfig->isSetFlag('wesupply_api/integration/wesupply_is_alias', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param array $needle
     * @param array $haystack
     * @param string $default
     * @return string
     */
    public function recursivelyGetArrayData($needle, $haystack, $default = '')
    {
        if (!is_array($haystack)) {
            return $default;
        }
        $result = $default;
        foreach ($needle as $key) {
            if (array_key_exists($key, $haystack)) {
                $result = $haystack[$key];
                if (is_array($result)) {
                    unset($needle[0]);
                    $remaining = array_values($needle);
                    return $this->recursivelyGetArrayData($remaining, $result, $default);
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTrackingInfoUri()
    {
        return self::WESUPPLY_TRACKING_INFO_URI;
    }

    /**
     * @return string
     */
    public function getStoreLocatorIdentifier()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_6/store_locator_cms', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getStoreDetailsIdentifier()
    {
        return $this->scopeConfig->getValue('wesupply_api/step_6/store_details_cms', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigDataByPath($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getTrackingInfoPageUrl()
    {
        return $this->_urlInterface->getBaseUrl() . $this->getTrackingInfoUri() . '/';
    }

    /**
     * @return string
     */
    public function getStoreLocatorPageUrl()
    {
        return $this->_urlInterface->getBaseUrl() . $this->getStoreLocatorUri() . '/';
    }

    /**
     * @return array
     */
    public function getAllowedCountries()
    {
        return $this->allowedCountries->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array|mixed
     */
    public function getShippingAllowedCountries()
    {
        if ($allowedCountries = $this->customerSession->getAllowedCountries()) {
            return $allowedCountries ?? [];
        }

        $this->weSupplyApi->setProtocol($this->getProtocol());
        $this->weSupplyApi->setApiPath($this->getWesupplyApiFullDomain());
        $this->weSupplyApi->setApiClientId($this->getWeSupplyApiClientId());
        $this->weSupplyApi->setApiClientSecret($this->getWeSupplyApiClientSecret());

        $allowedCountries = $this->weSupplyApi->getWeSupplyAllowedCountries();

        // memorize allowed countries
        if ($allowedCountries) {
            sort($allowedCountries);
            $this->customerSession->setAllowedCountries($allowedCountries);
        }

        return $allowedCountries ?? [];
    }

    /**
     * @return StoreInterface[]
     */
    public function getAllStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @param $scopeConfig
     * @return mixed
     */
    private function getBaseUrlByScopeConfigView($scopeConfig)
    {
        $isSecure = $this->scopeConfig->getValue('web/secure/use_in_frontend', $scopeConfig['scope_type'], $scopeConfig['scope_code']);
        $path = $isSecure ? 'web/secure/base_url' : 'web/unsecure/base_url';

        return $this->scopeConfig->getValue($path,$scopeConfig['scope_type'],$scopeConfig['scope_code']);
    }

    /**
     * @param int $scopeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getScopeConfigView($scopeId = 0)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        $scopeId = $scopeId ? $scopeId : (int) $this->request->getParam('store', 0);
        $code = $this->storeManager->getStore($scopeId)->getCode();

        if ($scopeId === 0) {
            $scopeId = (int) $this->request->getParam('website', 0);
            if ($scopeId) {
                $scope = ScopeInterface::SCOPE_WEBSITE;
                $code = $this->storeManager->getWebsite($scopeId)->getCode();
            }
        }

        return [
            'scope_type' => $scope,
            'scope_code' => $code
        ];
    }

    /**
     * @param bool $storeId
     * @return ThemeInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentTheme($storeId = false)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $themeId = $this->scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->themeProvider->getThemeById($themeId);
    }

    /**
     * Check if should exclude all orders from export
     *
     * @return bool
     */
    public function orderExportExcludeAll()
    {
        $exportOrders = $this->scopeConfig->getValue('wesupply_api/wesupply_order_export/wesupply_order_filter', ScopeInterface::SCOPE_STORE);
        if ($exportOrders === 'exclude_all') {
            return true;
        }

        return false;
    }

    /**
     * Check if specific filter rules are set up
     *
     * @return bool
     */
    public function hasOrderExportRules()
    {
        $exportOrders = $this->scopeConfig->getValue('wesupply_api/wesupply_order_export/wesupply_order_filter', ScopeInterface::SCOPE_STORE);
        if ($exportOrders === 'exclude_specific') {
            return true;
        }

        return false;
    }

    /**
     * Get the filter rules
     *
     * @return array
     */
    public function getOrderExportRules()
    {
        $filters = [];
        if ($this->hasOrderExportRules()) {
            foreach (self::FILTER_ORDERS_BY_MAP as $orderDataField => $systemConfig) {
                $filters[$orderDataField] =
                    $this->scopeConfig->getValue('wesupply_api/wesupply_order_export/' . $systemConfig, ScopeInterface::SCOPE_STORE)
                    ?? '';
            }
        }

        return $filters;
    }

    /**
     * @return array
     */
    public function getAttributesToBeExported()
    {
        if (
            !$this->orderExportExcludeAll() &&
            $attr = $this->scopeConfig->getValue('wesupply_api/wesupply_order_export/wesupply_order_product_attributes', ScopeInterface::SCOPE_STORE)
        ) {
            return explode(',', $attr) ?? [];
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getAttributesFetchPriority()
    {
        return $this->scopeConfig->getValue('wesupply_api/wesupply_order_export/wesupply_order_product_attributes_fetch', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null $field
     * @return mixed
     */
    public function getOrderExportSettings($field = null)
    {
        if ($field) {
            $field = '/' . trim($field, '/');
        }
        return $this->scopeConfig->getValue('wesupply_api/wesupply_order_export' . $field, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getWeightUnit()
    {
        if ($this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE) == 'lbs') {
            return 'lb';
        }

        if ($this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE) == 'kgs') {
            return 'kg';
        }

        return null;
    }

    /**
     * @return string
     */
    public function getMeasurementsUnit()
    {
        if ($this->getWeightUnit() === 'lb') { // imperial
            return 'in';
        }

        return 'cm'; // metric
    }

    /**
     * Convert camelcase string
     *
     * @param $string
     * @param $separator
     * @return string
     */
    public function fromCamelCase($string, $separator)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode($separator, $ret);
    }

    /**
     * Check if order have to be excluded from import or updates
     *
     * @param $orderId
     * @return bool
     */
    public function shouldIgnoreOrder($orderId)
    {
        if ($this->orderExportExcludeAll()) {
            /** exit if exclude all orders is set */
            return true;
        }

        if ($this->hasOrderExportRules()) {
            $order = $this->orderRepository->get($orderId);

            $filters = $this->getOrderExportRules();
            foreach ($filters as $attribute => $filterVal) {
                if (!$filterVal || empty($filterVal)) {
                    continue;
                }

                $filterByArr = explode(',', $filterVal);
                $compareVal = $this->{'get' . $attribute}($order);
                if (in_array($compareVal, $filterByArr)) {
                    /** exit if the condition is met */
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Custom method
     * for key defined under FILTER_ORDERS_BY_MAP const
     *
     * @param $order
     * @return string
     */
    public function getOrderShippingCountryCode($order)
    {
        /**
         * Downloadable product order have no shipping address
         * so, we will send the billing address instead
         */
        $countryCode = $order->getBillingAddress()->getCountryId();

        /** Shipping address look up */
        if ($order->getShippingAddress()) {
            $countryCode = $order->getShippingAddress()->getCountryId();
        }

        return $countryCode;
    }
}
