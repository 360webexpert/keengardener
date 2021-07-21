<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

/**
 * Sage Pay Token class
 */
class Token extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Post
     */
    private $_postApi;

    /**
     * @var Config
     */
    private $_config;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Model\Api\Post $postApi,
        Config $config,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_suiteLogger = $suiteLogger;
        $this->_logger = $context->getLogger();
        $this->_postApi = $postApi;
        $this->_config = $config;
    }

    /**
     * Init model
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('Ebizmarts\SagePaySuite\Model\ResourceModel\Token');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Saves a token to the db
     *
     * @param $customerId
     * @param $token
     * @param $ccType
     * @param $ccLast4
     * @param $ccExpMonth
     * @param $ccExpYear
     * @param $vendorname
     * @return $this
     */
    public function saveToken($customerId, $token, $ccType, $ccLast4, $ccExpMonth, $ccExpYear, $vendorname)
    {

        if (empty($customerId)) {
            return $this;
        }

        $this->setCustomerId($customerId);
        $this->setToken($token);
        $this->setCcType($ccType);
        $this->setCcLast4($ccLast4);
        $this->setCcExpMonth($ccExpMonth);
        $this->setCcExpYear($ccExpYear);
        $this->setVendorname($vendorname);
        $this->save();

        return $this;
    }

    /**
     * Gets an array of the tokens owned by a customer and for a certain vendorname
     *
     * @param $customerId
     * @param $vendorname
     * @return array
     */
    public function getCustomerTokens($customerId, $vendorname)
    {
        if (!empty($customerId)) {
            $this->setData([]);
            $this->getResource()->getCustomerTokens($this, $customerId, $vendorname);
            return $this->_data;
        }
        return [];
    }

    /**
     * Delete token from db and Sage Pay
     */
    public function deleteToken()
    {

        //delete from sagepay
        $this->_deleteFromSagePay();

        if ($this->getId()) {
            $this->delete();
        }
    }

    /**
     * delete token using Sage Pay API
     */
    private function _deleteFromSagePay()
    {
        try {
            if (empty($this->getVendorname()) || empty($this->getToken())) {
                //missing data to proceed
                return;
            }

            //generate delete POST request
            $data = [];
            $data["VPSProtocol"] = $this->_config->getVPSProtocol();
            $data["TxType"] = "REMOVETOKEN";
            $data["Vendor"] = $this->getVendorname();
            $data["Token"] = $this->getToken();

            //send POST to Sage Pay
            $this->_postApi->sendPost(
                $data,
                $this->_getRemoveServiceURL(),
                ["OK"]
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            //we do not show any error message to frontend
        }
    }

    private function _getRemoveServiceURL()
    {
        if ($this->_config->getMode() == Config::MODE_LIVE) {
            return Config::URL_TOKEN_POST_REMOVE_LIVE;
        } else {
            return Config::URL_TOKEN_POST_REMOVE_TEST;
        }
    }

    /**
     * load from db
     *
     * @param $tokenId
     * @return \Ebizmarts\SagePaySuite\Model\Token
     */
    public function loadToken($tokenId)
    {
        $token = $this->getResource()->getTokenById($tokenId);

        if ($token === null) {
            return null;
        }

        $this->setId($token["id"])
            ->setCustomerId($token["customer_id"])
            ->setToken($token["token"])
            ->setCcType($token["cc_type"])
            ->setCcLast4($token["cc_last_4"])
            ->setCcExpMonth($token["cc_exp_month"])
            ->setCcExpYear($token["cc_exp_year"])
            ->setVendorname($token["vendorname"])
            ->setCreatedAt($token["created_at"])
            ->setStoreId($token["store_id"]);

        return $this;
    }

    /**
     * Checks whether the token is owned by the customer
     *
     * @param $customerId
     * @return bool
     */
    public function isOwnedByCustomer($customerId)
    {
        if (empty($customerId) || empty($this->getId())) {
            return false;
        }
        return $this->getResource()->isTokenOwnedByCustomer($customerId, $this->getId());
    }

    /**
     * Checks whether the customer is using all the available token slots.
     * @param $customerId
     * @param $vendorname
     * @return bool
     */
    public function isCustomerUsingMaxTokenSlots($customerId, $vendorname)
    {
        if (empty($customerId)) {
            return true;
        }
        $this->setData([]);
        $this->getResource()->getCustomerTokens($this, $customerId, $vendorname);
        return count($this->_data) >= $this->_config->getMaxTokenPerCustomer();
    }
}
