<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (!$installer->tableExists('mageplaza_abandonedcart_reports_index')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_abandonedcart_reports_index'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Id')
                ->addColumn('period', Table::TYPE_DATE, null, [], 'Period')
                ->addColumn('store_id', Table::TYPE_INTEGER, null, [], 'Store ID')
                ->addColumn('cart_abandon_rate', Table::TYPE_FLOAT, null, [], 'Cart Abandon Rate')
                ->addColumn('successful_cart_rate', Table::TYPE_FLOAT, null, [], 'Successful Cart Rate')
                ->addColumn('total_abandoned_carts', Table::TYPE_INTEGER, null, [], 'Total Abandoned Carts')
                ->addColumn('total_abandoned_revenue', Table::TYPE_FLOAT, null, [], 'Total Abandoned Revenue')
                ->addColumn('number_of_successful_carts', Table::TYPE_INTEGER, null, [
                ], 'Number Of Successful Carts')
                ->addColumn('successful_carts_revenue', Table::TYPE_FLOAT, null, [], 'Successful Carts Revenue')
                ->addColumn('actionable_abandoned_carts', Table::TYPE_INTEGER, null, [
                ], 'Actionable Abandoned Carts')
                ->addColumn('recapturable_revenue', Table::TYPE_FLOAT, null, [], 'Recapturable Revenue')
                ->addColumn('recaptured_revenue', Table::TYPE_FLOAT, null, [], 'Recaptured Revenue')
                ->addColumn('recaptured_rate', Table::TYPE_FLOAT, null, [], 'Recaptured Rate')
                ->addColumn('total_email_abandoned_sent', Table::TYPE_INTEGER, null, [
                ], 'Total Email Abandoned Sent')
                ->addColumn('total_cart_checkout_sent', Table::TYPE_INTEGER, null, [], 'Total Cart Checkout Sent')
                ->addColumn('customer_group_id', Table::TYPE_INTEGER, null, [], 'Customer Group Id')
                ->setComment('Mageplaza Abandoned Cart Reports Index Table');
            $connection->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_abandonedcart_product_reports_index')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_abandonedcart_product_reports_index'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Id')
                ->addColumn('period', Table::TYPE_DATE, null, [], 'Period')
                ->addColumn('store_id', Table::TYPE_INTEGER, null, [], 'Store Id')
                ->addColumn('product_id', Table::TYPE_INTEGER, null, [], 'Product Id')
                ->addColumn('product_name', Table::TYPE_TEXT, 255, [], 'Product Name')
                ->addColumn('thumbnail', Table::TYPE_TEXT, 255, [], 'Thumbnail')
                ->addColumn('sku', Table::TYPE_TEXT, 255, [], 'SKU')
                ->addColumn('price', Table::TYPE_FLOAT, null, [], 'Price')
                ->addColumn('abandoned_time', Table::TYPE_INTEGER, null, [], 'Abandoned Time')
                ->addColumn('qty', Table::TYPE_INTEGER, null, [], 'Qty')
                ->addColumn('abandoned_revenue', Table::TYPE_DECIMAL, '12,4', [], 'Abandoned Revenue')
                ->addColumn('customer_group_id', Table::TYPE_INTEGER, null, [], 'Customer Group Id')
                ->setComment('Mageplaza Abandoned Cart Product Reports');
            $connection->createTable($table);
        }

        $connection->addColumn($installer->getTable('quote'), 'mp_abandoned_set_change', [
            'type'     => Table::TYPE_INTEGER,
            'nullable' => true,
            'comment'  => 'Mageplaza Abandoned Change'
        ]);

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $couponTable = $setup->getTable('salesrule_coupon');
            if (!$connection->tableColumnExists($couponTable, 'mp_generated_by_abandoned_cart')) {
                $connection->addColumn(
                    $couponTable,
                    'mp_generated_by_abandoned_cart',
                    [
                        'type'     => Table::TYPE_SMALLINT,
                        'nullable' => true,
                        'default'  => null,
                        'comment'  => '1 = Generated by Abandoned Cart Email'
                    ]
                );
            }
            if (!$connection->tableColumnExists($couponTable, 'mp_ace_expires_at')) {
                $connection->addColumn($couponTable, 'mp_ace_expires_at', [
                    'type'     => Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment'  => 'Coupon expiration date of Abandoned Cart Email',
                ]);
            }
        }

        $installer->endSetup();
    }
}
