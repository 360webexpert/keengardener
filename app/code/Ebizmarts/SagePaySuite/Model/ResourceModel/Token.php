<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\ResourceModel;

/**
 * Token resource model
 */
class Token extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('sagepaysuite_token', 'id');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get tokens by customer id and vendorname
     * @param \Ebizmarts\SagePaySuite\Model\Token $object
     * @param $customerId
     * @param $vendorname
     * @return array
     */
    public function getCustomerTokens(\Ebizmarts\SagePaySuite\Model\Token $object, $customerId, $vendorname)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id=?', $customerId)
            ->where('vendorname=?', $vendorname);

        $data = [];

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }

        if (count($data)) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $data;
    }

    /**
     * Get tokens by customer id and vendorname
     * @param $tokenId
     * @return array
     */
    public function getTokenById($tokenId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from($this->getMainTable())->where('id=?', $tokenId);

        $data = $connection->fetchRow($select);

        return $data;
    }

    /**
     * Checks if token is owned by customer
     * @param $customerId
     * @param $tokenId
     * @return bool
     */
    public function isTokenOwnedByCustomer($customerId, $tokenId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable(), 'id')
            ->where('customer_id=?', $customerId)
            ->where('id=?', $tokenId);

        $data = $connection->fetchOne($select);

        return ($data !== false);
    }
}
