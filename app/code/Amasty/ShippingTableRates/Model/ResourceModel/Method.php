<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel;

/**
 * Shipping Method Resource model
 */
class Method extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'amasty_table_method';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
