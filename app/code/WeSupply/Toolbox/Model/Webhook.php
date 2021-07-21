<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use WeSupply\Toolbox\Helper\Data as Helper;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class Webhook
 * @package WeSupply\Toolbox\Model
 */
class Webhook extends DataObject
{
    /**#@+
     * Constants
     */

    const ORDER_PREFIX = 'mage_';

    const RETURN_PARAMS = [
        'guid',
        'client-name',
        'reference'
    ];
    const RETURN_PARAMS_MAP = [
        'guid' => 'wesupply_api/integration/access_key',
        'client-name' => 'wesupply_api/integration/wesupply_subdomain',
        'reference' => null
    ];

    const PICKUP_PARAMS = [
        'guid',
        'client_name',
        'order_id',
        'pickup_store_id',
        'action',
        'item_ids',
        'item_quantities'
    ];

    const PICKUP_PARAMS_MAP = [
        'guid' => 'wesupply_api/integration/access_key',
        'client_name' => 'wesupply_api/integration/wesupply_subdomain',
        'order_id' => null,
        'pickup_store_id' => null,
        'action' => null,
        'item_ids' => null,
        'item_quantities' => null
    ];

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var WeSupplyApiInterface
     */
    private $weSupplyApi;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var $errorResponse
     */
    protected $errorResponse = [];

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Webhook constructor.
     * @param Helper $helper
     * @param WeSupplyApiInterface $weSupplyApi
     * @param ManagerInterface $messageManager
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        WeSupplyApiInterface $weSupplyApi,
        ManagerInterface $messageManager,
        Logger $logger,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->weSupplyApi = $weSupplyApi;
        $this->messageManager = $messageManager;
        $this->logger = $logger;

        parent::__construct($data);
    }

    /**
     * @return bool
     */
    public function canProceedsRequest()
    {
        if (!$this->helper->getConfigDataByPath('wesupply_api/integration/wesupply_enabled')) {
            $this->setError(__('WeSupply Toolbox extension is disabled'));

            return false;
        }

        return true;
    }


    /**
     * @param $requestType
     * @param $requestParams
     * @return bool
     */
    public function validateParams($requestType, $requestParams)
    {
        $requiredParams = $this->getParamsByPrefix($requestType);
        $requiredParamsMap = $this->getParamsMapByPrefix($requestType);

        $invalidParams = [];
        foreach ($requiredParams as $required) {
            if (
                !array_key_exists($required, $requestParams) ||
                (
                    $requiredParamsMap[$required] !== null &&
                    $requestParams[$required] !== $this->helper->getConfigDataByPath($requiredParamsMap[$required])
                )
            ) {
                $invalidParams[] = $required;
            }
        }

        if ($invalidParams) {
            $this->addData($invalidParams);
            $this->setError(__('Missing or invalid param(s): ' . $this->toString()));

            return false;
        }

        return true;
    }

    /**
     * @param $apiEndpoint
     * @param $requestType
     * @param $params
     * @return mixed
     */
    public function proceed($apiEndpoint, $requestType, $params)
    {
        $this->weSupplyApi->setProtocol($this->helper->getProtocol());
        $this->weSupplyApi->setApiPath($this->helper->getWesupplyApiFullDomain());
        $this->weSupplyApi->setApiClientId($this->helper->getWeSupplyApiClientId());
        $this->weSupplyApi->setApiClientSecret($this->helper->getWeSupplyApiClientSecret());

        $response = $this->weSupplyApi->grabUrl($apiEndpoint, $requestType, $params);
        if ($response === false) {
            $message = $this->collectSessionMessages();
            if (empty($message)) {
                $message = 'WeSupply returned an empty response. See log file for more details.';
            }
            $this->setError($message);
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getError()
    {
        return $this->errorResponse;
    }

    /**
     * @param $wsExternalOrderId
     * @return int
     */
    public function prepareOrderId($wsExternalOrderId): int
    {
        return (int) str_replace(self::ORDER_PREFIX, '', $wsExternalOrderId);
    }

    /**
     * Set error message
     * @param $message
     */
    private function setError($message)
    {
        $this->errorResponse = [
            'success' => false,
            'status-title' => 'Error!',
            'status-message' => $message
        ];
    }

    /**
     * @param $prefix
     * @return mixed
     */
    private function getParamsByPrefix($prefix)
    {
        $name = strtoupper($prefix) . '_PARAMS';
        return $this->getConstantByName($name);
    }

    /**
     * @param $prefix
     * @return mixed
     */
    private function getParamsMapByPrefix($prefix)
    {
        $name = strtoupper($prefix) . '_PARAMS_MAP';
        return $this->getConstantByName($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getConstantByName($name)
    {
        return constant("self::$name");
    }

    /**
     * @return string
     */
    private function collectSessionMessages()
    {
        $sessionMessages = $this->messageManager->getMessages()->getItems();
        foreach ($sessionMessages as $sessMsg) {
            $message = $sessMsg->getText() . ' ';
        }

        return $message ?? '';
    }
}
