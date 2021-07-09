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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\SeoCrosslinks\Setup
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
        if (!$installer->tableExists('mageplaza_seocrosslinks_term')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_seocrosslinks_term'))
                ->addColumn('term_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ], 'Term ID')
                ->addColumn('keyword', Table::TYPE_TEXT, 256, ['nullable => false'], 'Term Keyword')
                ->addColumn('link_title', Table::TYPE_TEXT, 64, ['nullable => true'], 'Link Title')
                ->addColumn('link_target', Table::TYPE_INTEGER, null, ['nullable => false'], 'Link Target')
                ->addColumn('reference', Table::TYPE_INTEGER, null, ['nullable => false'], 'Link to')
                ->addColumn('ref_static_url', Table::TYPE_TEXT, 64, ['nullable => true'], 'Custom Url')
                ->addColumn('ref_product_sku', Table::TYPE_TEXT, 64, ['nullable => true'], 'Product Sku')
                ->addColumn('ref_category_id', Table::TYPE_TEXT, 64, ['nullable => true'], 'Category Id')
                ->addColumn('from_date', Table::TYPE_TIMESTAMP, null, [], 'From Date')
                ->addColumn('to_date', Table::TYPE_TIMESTAMP, null, [], 'To Date')
                ->addColumn('limit', Table::TYPE_INTEGER, null, [], 'Term Limit Number Of Links Per Page')
                ->addColumn('rel', Table::TYPE_INTEGER, null, ['nullable => false'], 'Rel')
                ->addColumn('direction', Table::TYPE_INTEGER, null, ['nullable => false'], 'Direction')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [], 'Term Priority')
                ->addColumn('stores', Table::TYPE_TEXT, null, [], 'Stores')
                ->addColumn('apply_for', Table::TYPE_TEXT, null, ['nullable => false'], 'Apply for')
                ->addColumn('status', Table::TYPE_INTEGER, 1, ['nullable => false'], 'Term Status')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Term Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Term Updated At'
                )
                ->setComment('Term Table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
