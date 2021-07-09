<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Controller\Domain;

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
use WeSupply\Toolbox\Helper\Data as WsHelper;

/**
 * Class Save
 * @package WeSupply\Toolbox\Controller\Domainalias
 */
class Save extends Action
{
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
     * @var WsHelper
     */
    protected $wsHelper;

    /**
     * @var $successResponse
     */
    protected $successResponse;

    /**
     * @var $errorResponse
     */
    protected $errorResponse;

    /**
     * @var $params
     */
    private $params;

    /**
     * Save constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param TypeListInterface $cacheTypeList
     * @param WsHelper $wsHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList,
        WsHelper $wsHelper
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->wsHelper = $wsHelper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $this->params = $this->getRequest()->getParams();
        if (!$this->validateParams()) {
            return $resultJson->setData($this->errorResponse);
        }

        $this->saveConfigs([
            'wesupply_api/integration/wesupply_domain' => trim(preg_replace('(^https?://)', '', $this->params['domain']), '/'),
            'wesupply_api/integration/wesupply_is_alias' => $this->params['isAlias'],
        ], $this->params['storeIds']);

        if (!$this->params['isAlias']) {
            $this->resetConfigs([
                'wesupply_api/step_3/wesupply_order_view_iframe',
                'wesupply_api/step_3/wesupply_tracking_info_iframe',
            ], $this->params['storeIds']);
        }

        $this->flushCache(['config','layout', 'full_page']);

        return $resultJson->setData($this->successResponse);
    }

    /**
     * @return bool
     */
    private function validateParams()
    {
        if (
            !isset($this->params['guid']) ||
            !isset($this->params['storeIds']) ||
            !isset($this->params['domain']) ||
            !isset($this->params['isAlias'])
        ) {
            $this->setError(__('Missing required param(s)'));

            return false;
        }

        if ($this->params['guid'] !== $this->wsHelper->getGuid()) {
            $this->setError(__('Access Key does not match.'));

            return false;
        }

        return true;
    }

    /**
     * @param $configs
     * @param $storeIds
     * @return mixed
     */
    private function saveConfigs($configs, $storeIds)
    {
        $this->extractStoreIds($storeIds);
        $this->validateStoreIds($storeIds);

        if (!$storeIds) {
            $this->setError(__('Invalid Store ID(s)'));
            return $this->errorResponse;
        }

        foreach ($storeIds as $storeId) {
            $scope = $storeId === 0 ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
            foreach ($configs as $configPath => $value) {
                $this->configWriter->save($configPath, $value, $scope, $storeId);
            }
        }
    }

    /**
     * @param $configs
     * @param $storeIds
     * @return mixed
     */
    private function resetConfigs($configs, $storeIds)
    {
        $this->extractStoreIds($storeIds);
        $this->validateStoreIds($storeIds);

        if (!$storeIds) {
            $this->setError(__('Invalid Store ID(s)'));
            return $this->errorResponse;
        }

        foreach ($storeIds as $storeId) {
            $scope = $storeId === 0 ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES;
            foreach ($configs as $configPath) {
                $this->configWriter->save($configPath, 0, $scope, $storeId);
            }
        }
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
                unset($storeIds[$key]);
                $removed++;
            }
        }

        return $storeIds;
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
     * @param $message
     */
    private function setError($message)
    {
        $this->errorResponse = [
            'error' => true,
            'response_code' => 503,
            'message' => $message
        ];
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
