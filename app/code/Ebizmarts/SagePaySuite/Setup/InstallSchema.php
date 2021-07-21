<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /** @var SplitDatabaseConnectionProvider */
    private $connectionProvider;

    public function __construct(SplitDatabaseConnectionProvider $connectionProvider)
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tableName = $setup->getTable('sales_order_payment');

        $this
            ->connectionProvider
            ->getSalesConnection($setup)
            ->modifyColumn(
                $tableName,
                "last_trans_id",
                [
                'type' => Table::TYPE_TEXT,
                'length' => 100,
                'nullable' => false
                ]
            );

        $installer->endSetup();
    }
}
