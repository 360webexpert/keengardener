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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\AbandonedCart\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('mageplaza_abandonedcart_logs')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_abandonedcart_logs'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Log Id')
                ->addColumn('subject', Table::TYPE_TEXT, 255, [], 'Subject')
                ->addColumn('customer_email', Table::TYPE_TEXT, 255, [], 'Customer email')
                ->addColumn('coupon_code', Table::TYPE_TEXT, 255, [], 'Coupon Code')
                ->addColumn('sender', Table::TYPE_TEXT, 255, [], 'Sender')
                ->addColumn('customer_name', Table::TYPE_TEXT, 255, [], 'Customer Name')
                ->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Quote Id'
                )
                ->addColumn(
                    'sequent_number',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => '1'
                    ],
                    'Sequent number'
                )
                ->addColumn(
                    'recovery',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Recovery'
                )
                ->addColumn('email_content', Table::TYPE_TEXT, '64k', [], 'Email Content')
                ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false], 'Status')
                ->addColumn('display', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => '1'], 'Display')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addIndex(
                    $installer->getIdxName('mageplaza_abandonedcart_logs', ['status', 'subject']),
                    ['status', 'subject']
                )
                ->setComment('Abandoned Cart Email Logs');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_abandonedcart_logs_token')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_abandonedcart_logs_token'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Id')
                ->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Quote Id'
                )
                ->addColumn('config_id', Table::TYPE_TEXT, 255, [], 'General configuration email id')
                ->addColumn(
                    'checkout_token',
                    Table::TYPE_TEXT,
                    128,
                    [
                        'nullable' => true,
                        'default'  => null
                    ],
                    'Checkout token'
                )
                ->addColumn(
                    'checkout_token_created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true,
                        'default'  => null
                    ],
                    'Checkout token creation time'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_abandonedcart_logs_token', ['quote_id', 'checkout_token']),
                    ['quote_id', 'checkout_token']
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_abandonedcart_logs_token', 'quote_id', 'quote', 'entity_id'),
                    'quote_id',
                    $installer->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment('Abandoned Cart Email Token');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
