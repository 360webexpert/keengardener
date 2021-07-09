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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\SeoRule\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            if ($installer->tableExists('mageplaza_seorule_rule')) {
                $installer->getConnection()->dropTable($installer->getTable('mageplaza_seorule_rule'));
            }
            if ($installer->tableExists('mageplaza_seorule_rule_product')) {
                $installer->getConnection()->dropTable($installer->getTable('mageplaza_seorule_rule_product'));
            }

            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_seorule_rule'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ], 'Rule ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Rule Name')
                ->addColumn('entity_type', Table::TYPE_TEXT, 255, ['nullable' => false], 'Rule Entity Type')
                ->addColumn('apply_template', Table::TYPE_TEXT, 255, ['nullable' => false], 'Rule Apply Template')
                ->addColumn('meta_title', Table::TYPE_TEXT, '64k', [], 'Rule Meta Title Template')
                ->addColumn('meta_description', Table::TYPE_TEXT, '64k', [], 'Rule Meta Description Template')
                ->addColumn('meta_keywords', Table::TYPE_TEXT, '64k', [], 'Rule Meta Keywords Template')
                ->addColumn('categorys', Table::TYPE_TEXT, 255, [], 'Category conditions')
                ->addColumn('pages', Table::TYPE_TEXT, 255, [], 'Page conditions')
                ->addColumn('meta_robots', Table::TYPE_TEXT, null, [], 'Rule Robots Template')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('status', Table::TYPE_INTEGER, 1, [], 'Rule Status')
                ->addColumn('stores', Table::TYPE_TEXT, 64, [], 'Rule Store View')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [], 'Sort Order')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Rule created at'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Rule update at'
                )
                ->setComment('Rule Table');

            $installer->getConnection()->createTable($table);

            if ($installer->tableExists('mageplaza_seorule_meta')) {
                $installer->getConnection()->dropTable($installer->getTable('mageplaza_seorule_meta'));
            }

            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_seorule_meta'))
                ->addColumn('meta_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ], 'Meta Data ID')
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Rule ID')
                ->addColumn('entity_type', Table::TYPE_TEXT, 255, ['nullable => false'], 'Rule Entity Type')
                ->addColumn('product_id', Table::TYPE_TEXT, 255, [], 'Product Id')
                ->addColumn('category_id', Table::TYPE_TEXT, 255, [], 'Product Id')
                ->addColumn('page_id', Table::TYPE_TEXT, 255, [], 'Product Id')
                ->addColumn('meta_title', Table::TYPE_TEXT, '64k', [], 'Meta Data Meta Title')
                ->addColumn('meta_description', Table::TYPE_TEXT, '64k', [], 'Meta Data Meta Description')
                ->addColumn('meta_keywords', Table::TYPE_TEXT, '64k', [], 'Meta Data Meta Keywords')
                ->addColumn('meta_robots', Table::TYPE_TEXT, 255, [], 'Meta Data Meta Robots')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [], 'Sort Order')
                ->addIndex($installer->getIdxName('mageplaza_seorule_meta', ['rule_id']), ['rule_id'])
                ->addForeignKey(
                    $installer->getFkName('mageplaza_seorule_meta', 'rule_id', 'mageplaza_seorule_rule', 'rule_id'),
                    'rule_id',
                    $installer->getTable('mageplaza_seorule_rule'),
                    'rule_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Meta Data Table');

            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('mageplaza_seorule_meta'),
                'apply_template',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'comment'  => 'Rule Apply Template',
                    'nullable' => false
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('mageplaza_seorule_meta'),
                'stores',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 64,
                    'comment'  => 'Rule Stores',
                    'nullable' => false
                ]
            );
        }

        $setup->endSetup();
    }
}
