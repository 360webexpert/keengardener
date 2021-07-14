<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel;

/**
 * Method Labels Resource
 */
class Label extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_method_label';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'entity_id');
    }
}
