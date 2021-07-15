<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel\Method;

/**
 * Shipping Method Resource Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShippingTableRates\Model\Method::class,
            \Amasty\ShippingTableRates\Model\ResourceModel\Method::class
        );
    }

    public function addStoreFilter($storeId)
    {
        $storeId = (int)$storeId;
        $this->getSelect()->where('stores="" OR FIND_IN_SET("' . $storeId . '", `stores`)');

        return $this;
    }

    public function addCustomerGroupFilter($groupId)
    {
        $groupId = (int)$groupId;
        $this->getSelect()->where('cust_groups="" OR FIND_IN_SET("' . $groupId . '", `cust_groups`)');

        return $this;
    }

    public function hashMinRate()
    {
        return $this->_toOptionHash('id', 'min_rate');
    }

    public function hashMaxRate()
    {
        return $this->_toOptionHash('id', 'max_rate');
    }

    public function joinLabels($modelId)
    {
        $this->getSelect()->joinLeft(
            ['label' => $this->getTable('amasty_method_label')],
            'main_table.id = label.method_id'
        )->where(
            'main_table.id=?',
            $modelId
        );

        return $this;
    }
}
