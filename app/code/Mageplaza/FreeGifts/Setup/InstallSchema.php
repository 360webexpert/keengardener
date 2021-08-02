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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mageplaza\FreeGifts\Helper\Rule as RuleHelper;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\FreeGifts\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const FREEGIFTS_RULES_TABLE = 'mageplaza_freegifts_rules';

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
        $connection = $installer->getConnection();

        // mageplaza_freegifts_rules table
        if ($installer->tableExists(self::FREEGIFTS_RULES_TABLE)) {
            $connection->dropTable($installer->getTable(self::FREEGIFTS_RULES_TABLE));
        }

        $table = $connection->newTable($installer->getTable(self::FREEGIFTS_RULES_TABLE));
        $columns = $this->getFreeGiftColumns();
        foreach ($columns as $name => $column) {
            $table->addColumn($name, $column['type'], $column['size'], $column['options'], $column['comment']);
        }
        $table->setComment('Free Gifts Rules Table');
        $connection->createTable($table);

        // Additional column for Quote_Item Table
        $quoteItemTable = $installer->getTable('quote_item');
        if (!$connection->tableColumnExists($quoteItemTable, RuleHelper::QUOTE_RULE_ID)) {
            $connection->addColumn($quoteItemTable, RuleHelper::QUOTE_RULE_ID, [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Mageplaza Free Gift Rule Id',
            ]);
        }

        // Additional column for Sales_Order_Item
        $quoteItemTable = $installer->getTable('sales_order_item');
        if (!$connection->tableColumnExists($quoteItemTable, RuleHelper::QUOTE_RULE_ID)) {
            $connection->addColumn($quoteItemTable, RuleHelper::QUOTE_RULE_ID, [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Mageplaza Free Gift Rule Id',
            ]);
        }

        $installer->endSetup();
    }

    /**
     * @return array
     */
    public function getFreeGiftColumns()
    {
        return [
            'rule_id' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ],
                'comment' => 'Rule ID',
            ],
            'name' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => ['nullable' => false,],
                'comment' => 'Name',
            ],
            'status' => [
                'type' => Table::TYPE_SMALLINT,
                'size' => 1,
                'options' => ['nullable' => false,],
                'comment' => 'Status Of Rule',
            ],
            'website_id' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => ['nullable' => false],
                'comment' => 'Website Id',
            ],
            'customer_group_ids' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => ['nullable' => false],
                'comment' => 'Customer Group',
            ],
            'conditions_serialized' => [
                'type' => Table::TYPE_TEXT,
                'size' => '2M',
                'options' => ['nullable' => false],
                'comment' => 'Rule Conditions Serialized',
            ],
            'from_date' => [
                'type' => Table::TYPE_DATE,
                'size' => null,
                'options' => [],
                'comment' => 'Active From',
            ],
            'to_date' => [
                'type' => Table::TYPE_DATE,
                'size' => null,
                'options' => [],
                'comment' => 'Active To',
            ],
            'priority' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0
                ],
                'comment' => 'Priority',
            ],
            'type' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => ['nullable' => false],
                'comment' => 'Action Type Add Gift',
            ],
            'apply_for' => [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'options' => ['nullable' => false],
                'comment' => 'Rule Apply For',
            ],
            'number_gift_allowed' => [
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => [
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => 0,
                ],
                'comment' => 'Number Gift Allowed',
            ],
            'allow_notice' => [
                'type' => Table::TYPE_SMALLINT,
                'size' => 1,
                'options' => [],
                'comment' => 'Allow Show Notice',
            ],
            'use_config_allow_notice' => [
                'type' => Table::TYPE_SMALLINT,
                'size' => 1,
                'options' => ['nullable' => true],
                'comment' => 'Allow Show Notice Default Config',
            ],
            'notice' => [
                'type' => Table::TYPE_TEXT,
                'size' => '2M',
                'options' => [],
                'comment' => 'Notice Content',
            ],
            'use_config_notice' => [
                'type' => Table::TYPE_SMALLINT,
                'size' => 1,
                'options' => ['nullable' => true],
                'comment' => 'Notice Content Default Config',
            ],
            'discard_subsequent_rules' => [
                'type' => Table::TYPE_SMALLINT,
                'size' => 1,
                'options' => ['nullable' => false, 'default' => 1],
                'comment' => 'Discard Subsequent Rules',
            ],
            'description' => [
                'type' => Table::TYPE_TEXT,
                'size' => '2M',
                'options' => ['nullable' => true],
                'comment' => 'Rule Description',
            ],
            'gifts' => [
                'type' => Table::TYPE_TEXT,
                'size' => '2M',
                'options' => ['nullable' => false],
                'comment' => 'List Gift Serialized',
            ],
        ];
    }
}
