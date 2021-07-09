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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\Shopbybrand\Setup
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
        if (!$installer->tableExists('mageplaza_brand')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_brand'))
                ->addColumn('brand_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ], 'Brand ID')
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Attribute Option Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Config Scope Id'
                )
                ->addColumn('page_title', Table::TYPE_TEXT, 255, [], 'Brand Page Title')
                ->addColumn('url_key', Table::TYPE_TEXT, 255, ['nullable => false'], 'Url Key')
                ->addColumn('image', Table::TYPE_TEXT, 255, [], 'Brand Brand Image')
                ->addColumn('is_featured', Table::TYPE_INTEGER, 1, [], 'Brand Featured Brand')
                ->addColumn('short_description', Table::TYPE_TEXT, '64k', [], 'Brand Short Description')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Brand Description')
                ->addColumn('static_block', Table::TYPE_TEXT, null, [], 'Static Block')
                ->addColumn('meta_title', Table::TYPE_TEXT, null, [], 'Meta Title')
                ->addColumn('meta_keywords', Table::TYPE_TEXT, null, [], 'Meta Keywords')
                ->addColumn('meta_description', Table::TYPE_TEXT, null, [], 'Meta Description')
                ->addForeignKey(
                    $installer->getFkName('mageplaza_brand', 'option_id', 'eav_attribute_option', 'option_id'),
                    'option_id',
                    $installer->getTable('eav_attribute_option'),
                    'option_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_brand', ['option_id', 'store_id'], true),
                    ['option_id', 'store_id'],
                    ['type' => 'unique']
                )
                ->setComment('Brand Option Table');

            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
