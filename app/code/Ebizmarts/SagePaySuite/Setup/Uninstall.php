<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /** @var SplitDatabaseConnectionProvider */
    private $connectionProvider;

    public function __construct(SplitDatabaseConnectionProvider $connectionProvider)
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->allowNullTransactionId($setup);
        $this->dropSagePayTokensTable($setup);
        $this->removeFraudCheckColumn($setup);

        $setup->endSetup();
    }

    private function allowNullTransactionId(SchemaSetupInterface $setup)
    {
        $this->connectionProvider
            ->getSalesConnection($setup)
            ->modifyColumn(
                $setup->getTable('sales_order_payment'),
                "last_trans_id",
                ['nullable' => true]
            );
    }

    private function dropSagePayTokensTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropTable($setup->getTable('sagepaysuite_token'));
    }

    private function removeFraudCheckColumn(SchemaSetupInterface $setup)
    {
        $this->connectionProvider
            ->getSalesConnection($setup)
            ->dropColumn(
                $setup->getTable('sales_payment_transaction'),
                'sagepaysuite_fraud_check'
            );
    }
}
