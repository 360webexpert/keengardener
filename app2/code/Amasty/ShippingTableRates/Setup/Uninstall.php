<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Setup;

use Amasty\ShippingTableRates\Model\ResourceModel\Label;
use Amasty\ShippingTableRates\Model\ResourceModel\Method;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->getConnection()
            ->dropTable($setup->getTable(Label::MAIN_TABLE));
        $setup->getConnection()
            ->dropTable($setup->getTable(Rate::MAIN_TABLE));
        $setup->getConnection()
            ->dropTable($setup->getTable(Rate::MAIN_TABLE . '_replica'));
        $setup->getConnection()
            ->dropTable($setup->getTable(Method::MAIN_TABLE));
    }
}
