<?php

namespace WeSupply\Toolbox\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Phrase;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

class ConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var WeSupplyApiInterface
     */
    protected $_weSupplyApi;

    /**
     * @var Helper
     */
    private $_helper;

    /**
     * API Credentials
     */
    private $apiClientId;
    private $apiClientSecret;

    private $groups = [];

    /**
     * ConfigPlugin constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param WeSupplyApiInterface $weSupplyApi
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        WeSupplyApiInterface $weSupplyApi,
        Helper $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->messageManager = $context->getMessageManager();
        $this->_weSupplyApi = $weSupplyApi;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @return \Magento\Config\Model\Config
     */
    public function beforeSave(\Magento\Config\Model\Config $subject)
    {
        if ($subject->getSection() != 'wesupply_api') {
            // exit if section is not 'wesupply_api'
            return $subject;
        }

        $this->groups = $subject->getGroups();
        $params = $this->prepareApiParams($subject);

        if ($params['has_error']) {
            $this->messageManager->addErrorMessage(__($params['validation_message']));
            // reset connection status flag and disable estimation service
            $this->resetConnectionStatus($subject);
            $this->resetConfigValues(['step_5/enable_delivery_estimations']);

            $subject->setData('groups', $this->groups);

            return $subject;
        }

        // check WeSupply api credentials
        $this->_weSupplyApi->setProtocol($this->_helper->getProtocol());
        $this->_weSupplyApi->setApiPath($this->_helper->getWesupplyApiFullDomain());
        $this->_weSupplyApi->setApiClientId($this->apiClientId);
        $this->_weSupplyApi->setApiClientSecret($this->apiClientSecret);

        $apiResponse = $this->_weSupplyApi->checkApiCredentials();
        if (!$apiResponse) { // couldn't get a valid token
            $this->messageManager->addErrorMessage(__('A WeSupply connection error has been detected during configuration saving. Go to your WeSupply Platform and (re)connect your Magento Store.'));
            // reset connection status flag and reset sms notification
            $this->resetConnectionStatus($subject);
            $this->resetConfigValues(['step_5/enable_delivery_estimations']);

            $subject->setData('groups', $this->groups);

            return $subject;
        }

        // check for AccessKey change
        $accessKeyIsChanged = isset($this->groups['integration']['fields']['access_key']) ? $this->_helper->getGuid() != $this->groups['integration']['fields']['access_key'] : false;
        if ($accessKeyIsChanged) {
            $this->messageManager->addErrorMessage(__('It seems that the AccessKey has changed. You have to reconnect your WeSupply Platform using the currently displayed AccessKey.'));
        }

        // set connection status flag and continue
        $this->setConnectionStatus($subject);

        /**
         * Check services and options availabilities
         */
        $enableDeliveryEstimation = $this->_helper->recursivelyGetArrayData(['step_5','fields','enable_delivery_estimations','value'], $this->groups, false);
        $orderViewIframeModal = $this->_helper->recursivelyGetArrayData(['step_3','fields','wesupply_order_view_iframe','value'], $this->groups, false);
        $trackingInfoIframeMagento = $this->_helper->recursivelyGetArrayData(['step_3','fields','wesupply_tracking_info_iframe','value'], $this->groups, false);

        if (
            !$enableDeliveryEstimation &&
            !$orderViewIframeModal &&
            !$trackingInfoIframeMagento
        ) {
            // no check needed
            return $subject;
        }

        if ($enableDeliveryEstimation) { // delivery estimate enabling required
            $this->checkEstimationServiceAvailability($subject);
        }

        if ($orderViewIframeModal) { // has domain alias required
            $this->checkOptionAvailability(
                $subject, ['step_3/wesupply_order_view_iframe'],
                'In order to enable the "Open in Modal" option for the "Select Order View Behavior" configuration setting, make sure you have configured a Domain Alias under your WeSupply account.'
            );
        }

        if ($trackingInfoIframeMagento) { // has domain alias required
            $this->checkOptionAvailability(
                $subject, ['step_3/wesupply_tracking_info_iframe'],
                'In order to enable the "Open in Magento" option for the "Select Tracking Info Behavior" configuration setting, make sure you have configured a Domain Alias under your WeSupply account.'
            );
        }

        return $subject;
    }

    /**
     * @param $subject
     */
    private function checkEstimationServiceAvailability(&$subject)
    {
        $serviceIsAvailable = $this->_weSupplyApi->checkServiceAvailability('estimation');

        if ($serviceIsAvailable === false) { // API credentials check has thrown an exception
            $this->messageManager->addErrorMessage(__('Something went wrong. Check error log files for more details'));
            $this->resetConfigValues(['step_5/enable_delivery_estimations']);
            $subject->setData('groups', $this->groups);
        }

        if (isset($serviceIsAvailable['allowed']) && $serviceIsAvailable['allowed'] === false) {
            $this->messageManager->addErrorMessage(__('In order to enable Delivery Estimation functionality, make sure this addon is activated under your WeSupply account. Please upgrade your plan.'));
            $this->resetConfigValues(['step_5/enable_delivery_estimations']);
            $subject->setData('groups', $this->groups);
        }
    }

    /**
     * @param $subject
     * @param $optionPath
     * @param $message
     */
    private function checkOptionAvailability(&$subject, $optionPath, $message)
    {
        if (!$this->_helper->weSupplyHasDomainAlias()) {
            $this->messageManager->addErrorMessage(__($message));
            $this->resetConfigValues($optionPath);
            $subject->setData('groups', $this->groups);
        }
    }

    /**
     * @param $subject
     * @return array
     */
    private function prepareApiParams($subject)
    {
        $response = [
            'has_error' => false,
            'validation_message' => ''
        ];

        $scope = $this->getCurrentScope($subject);
        $scopeId = $this->getCurrentScopeId($subject);

        $params['apiClientId'] = $this->scopeConfig->getValue('wesupply_api/integration/wesupply_client_id', $scope, $scopeId)
            ?? $this->_helper->getWeSupplyApiClientId();

        $params['apiClientSecret'] = $this->scopeConfig->getValue('wesupply_api/integration/wesupply_client_secret', $scope, $scopeId)
            ?? $this->_helper->getWeSupplyApiClientSecret();

        $validationMessage = $this->_validateParams($params);
        if ($validationMessage) {
            $response['has_error'] = true;
            $response['validation_message'] = $validationMessage;
        }

        return $response;
    }

    /**
     * @param $params
     * @return bool|Phrase
     */
    private function _validateParams($params)
    {
        $this->apiClientId = trim($params['apiClientId']) ?? '';
        $this->apiClientSecret = trim($params['apiClientSecret']) ?? '';

        return false;
    }

    /**
     * @param $subject
     */
    protected function resetConnectionStatus($subject)
    {
        $scope = $this->getCurrentScope($subject);
        $scopeId = $this->getCurrentScopeId($subject);
        $this->configWriter->save('wesupply_api/integration/wesupply_connection_status', 0, $scope, $scopeId);
    }

    /**
     * @param $subject
     */
    protected function setConnectionStatus($subject)
    {
        $scope = $this->getCurrentScope($subject);
        $scopeId = $this->getCurrentScopeId($subject);
        $this->configWriter->save('wesupply_api/integration/wesupply_connection_status', 1, $scope, $scopeId);
    }

    /**
     * @param array $fields
     */
    protected function resetConfigValues($fields = [])
    {
        foreach ($fields as $field) {
            $fieldArr = explode('/', $field);
            $this->groups[$fieldArr[0]]['fields'][$fieldArr[1]]['value'] = 0;
        }
    }

    /**
     * @param $subject
     * @return string
     */
    protected function getCurrentScope($subject)
    {
        if ($subject->getStore()) {
            return 'stores';
        }

        if ($subject->getWebsite()) {
            return 'websites';
        }

        return 'default';
    }

    /**
     * @param $subject
     * @return string
     */
    protected function getCurrentScopeId($subject)
    {
        if ($subject->getStore()) {
            return  $subject->getStore();
        }

        if ($subject->getWebsite()) {
            return $subject->getWebsite();
        }

        return '0';
    }
}
