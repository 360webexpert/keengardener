<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Resource collection for report rows
 */
namespace Ebizmarts\SagePaySuite\Model\ResourceModel\FraudReport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Mapping for fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    protected $_map = [
        'fields' => [
            'additional_information' => 'payments.additional_information'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Resource initializing
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init(
            'Magento\Sales\Model\Order\Payment\Transaction',
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction'
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * @return $this
     */
    // @codingStandardsIgnoreStart
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['payments' => $this->getTable('sales_order_payment')],
            'payments.entity_id = main_table.payment_id'
        )->where("sagepaysuite_fraud_check=1");
        return $this;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'created_at') {
            $field = 'main_table.' . $field;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
