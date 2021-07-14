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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\LayeredNavigationUltimate\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            if (!$installer->tableExists('layered_product_pages')) {
                $table = $installer->getConnection()->newTable($installer->getTable('layered_product_pages'))
                    ->addColumn(
                        'page_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
                    )
                    ->addColumn('name', Table::TYPE_TEXT, '256', [])
                    ->addColumn('page_title', Table::TYPE_TEXT, '256', [])
                    ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
                    ->addColumn('route', Table::TYPE_TEXT, '255', [])
                    ->addColumn('store_ids', Table::TYPE_TEXT, '255', ['nullable' => false, 'default' => '0'])
                    ->addColumn('position', Table::TYPE_TEXT, '255', [])
                    ->addColumn('default_attributes', Table::TYPE_TEXT, '2M', [])
                    ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
                    ->addColumn('meta_title', Table::TYPE_TEXT, '256', [])
                    ->addColumn('meta_keywords', Table::TYPE_TEXT, '256', [])
                    ->addColumn('meta_description', Table::TYPE_TEXT, '2M', [])
                    ->addIndex(
                        $installer->getIdxName('layered_product_pages', 'route'),
                        'route',
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Mageplaza LayeredNavigation custom products page table');

                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}
