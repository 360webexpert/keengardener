<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Framework\DataObject;
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
     * Webhook constructor.
     * @param Helper $helper
     * @param WeSupplyApiInterface $weSupplyApi
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        WeSupplyApiInterface $weSupplyApi,
        Logger $logger,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->weSupplyApi = $weSupplyApi;
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
            $this->setError(__('WeSupply returned an empty response. See log file for more details.'));
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
     * Set error message
     * @param $message
     */
    private function setError($message)
    {
        $this->errorResponse = [
            'success' => false,
            'status-title' => __('Error!'),
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
}
