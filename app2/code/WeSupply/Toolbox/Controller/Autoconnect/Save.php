<?php

namespace WeSupply\Toolbox\Controller\Autoconnect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Api\Authorize;
use WeSupply\Toolbox\Helper\Data as Helper;
use WeSupply\Toolbox\Logger\Logger;

class Save extends Action
{
    /**
     * @var Authorize
     */
    protected $_auth;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var $params
     */
    private $params;

    /**
     * @var $errorResponse
     */
    protected $errorResponse;

    /**
     * @var $successResponse
     */
    protected $successResponse;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param TypeListInterface $cacheTypeList
     * @param Helper $helper
     * @param Authorize $authorize
     * @param JsonFactory $resultJsonFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList,
        Helper $helper,
        Authorize $authorize,
        JsonFactory $resultJsonFactory,
        Logger $logger
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->_helper = $helper;
        $this->_auth = $authorize;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->validateParams()) {
            return $resultJson->setData($this->errorResponse);
        }

        $authResponse = $this->_auth->authorize($this->params['guid'], $this->params['connection']);
        if (!$authResponse) {
            $this->setError($this->_auth->error);
            return $resultJson->setData($this->errorResponse);
        }

        $saveResponse = $this->saveConfigs([
            'wesupply_api/integration/wesupply_enabled' => 1,
            'wesupply_api/integration/wesupply_connection_status' => 1,
            'wesupply_api/integration/wesupply_subdomain' => $this->params['ClientName'] ?? '',
            'wesupply_api/integration/wesupply_client_id' => $this->getClientIdFromAuth($authResponse) ?? '',
            'wesupply_api/integration/wesupply_client_secret' => $this->getClientSecretFromAuth($authResponse) ?? ''
        ], $this->params['storeIds']);

        return $resultJson->setData($saveResponse);
    }

    /**
     * @return bool
     */
    private function validateParams()
    {
        $this->params = $this->getRequest()->getParams();

        if (
            !isset($this->params['guid']) ||
            !isset($this->params['endpoint']) ||
            !isset($this->params['ClientName']) ||
            !isset($this->params['storeIds']) ||
            !isset($this->params['connection'])
        ) {
            $this->setError(__('Missing required param(s)'));

            return false;
        }

        if ($this->params['guid'] !== $this->_helper->getGuid()) {
            $this->setError(__('Access Key does not match.'));

            return false;
        }

        return true;
    }

    /**
     * Set error message
     * @param $message
     */
    private function setError($message)
    {
        $this->errorResponse = [
            'response_code' => 503,
            'error' => true,
            'message' => $message
        ];
    }

    /**
     * Set success message
     */
    private function setSuccess()
    {
        $this->successResponse = [
            'response_code' => 200,
            'error' => false,
            'message' => 'OK'
        ];
    }

    /**
     * @param $authResponse
     * @return string
     */
    private function getClientIdFromAuth($authResponse)
    {
        $params = $this->extractParams($authResponse);

        return isset($params['id']) ? $params['id'] : '';
    }

    /**
     * @param $authResponse
     * @return string
     */
    private function getClientSecretFromAuth($authResponse)
    {
        $params = $this->extractParams($authResponse);

        return isset($params['secret']) ? $params['secret'] : '';
    }

    /**
     * @param $authResponse
     * @return mixed
     */
    private function extractParams($authResponse)
    {
        parse_str($authResponse, $output);

        return $output;
    }

    /**
     * @param $configs
     * @param $storeIds
     * @return array
     */
    private function saveConfigs($configs, $storeIds)
    {
        $this->extractStoreIds($storeIds);
        $this->validateStoreIds($storeIds);

        if (!$storeIds) {
            $this->setError(__('Store id(s) not found'));
            return $this->errorResponse;
        }

        foreach ($storeIds as $storeId) {
            $scope = $storeId === 0 ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
            foreach ($configs as $configPath => $value) {
                $this->configWriter->save($configPath, $value, $scope, $storeId);
            }
        }

        $this->flushCache(['config','layout', 'full_page']);

        return $this->successResponse;
    }

    /**
     * @param $storeIds
     * @return array
     */
    private function extractStoreIds(&$storeIds)
    {
        /**
         * Allowed values:
         * 'all' for all store views
         * single id, ex. '1' for a specific store view
         * comma separated ids, ex. '1,4,7' for multiple store views
         */
        preg_match_all('/(.*?)(?:,|;|-|$)/i', $storeIds, $output, PREG_PATTERN_ORDER);
        $storeIds = array_filter($output[1], function ($val) {
            return $val !== '' && $val !== null;
        });

        return $storeIds;
    }

    /**
     * @param $storeIds
     * @return mixed
     */
    private function validateStoreIds(&$storeIds)
    {
        $allStores = array_values(array_map(function ($store) {
            return $store->getStoreId();
        }, $this->storeManager->getStores()));

        $removed = 0;
        foreach ($storeIds as $key => $storeId) {
            if (trim(strtolower($storeId)) === 'all') {
                $storeIds[$key] = 0;
                continue;
            }
            $storeIds[$key] = (int) $storeId;

            if (!in_array($storeId, $allStores)) {
                // @TODO count number of removed id(s) and log them to response
                unset($storeIds[$key]);
                $removed++;
            }
        }

        return $storeIds;
    }

    /**
     * @param $cacheTypes
     */
    private function flushCache($cacheTypes)
    {
        foreach ($cacheTypes as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        $this->setSuccess();
    }
}
