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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\Shopbybrand\Setup
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
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            if (!$installer->tableExists('mageplaza_shopbybrand_category')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_shopbybrand_category'))
                    ->addColumn('cat_id', Table::TYPE_INTEGER, 10, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ])
                    ->addColumn('name', Table::TYPE_TEXT, '256', [])
                    ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
                    ->addColumn('url_key', Table::TYPE_TEXT, '255', [])
                    ->addColumn('store_ids', Table::TYPE_TEXT, '255', ['nullable' => false, 'default' => '0'])
                    ->addColumn('meta_title', Table::TYPE_TEXT, '256', [])
                    ->addColumn('meta_keywords', Table::TYPE_TEXT, '64k', [])
                    ->addColumn('meta_description', Table::TYPE_TEXT, '2M', [])
                    ->addColumn('meta_robots', Table::TYPE_TEXT, null, [], 'Category Meta Robots')
                    ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Category Created At')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Tag Updated At')
                    ->addIndex(
                        $installer->getIdxName('mageplaza_shopbybrand_category', 'url_key'),
                        'url_key',
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Mageplaza Shopbybrand category table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_shopbybrand_brand_category')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_shopbybrand_brand_category'))
                    ->addColumn('cat_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ])
                    ->addColumn(
                        'option_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'unsigned' => true, 'primary' => true]
                    )
                    ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0])
                    ->addIndex(
                        $installer->getIdxName('mageplaza_shopbybrand_brand_category', ['option_id']),
                        ['option_id']
                    )
                    ->addIndex($installer->getIdxName('mageplaza_shopbybrand_brand_category', ['cat_id']), ['cat_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_shopbybrand_brand_category',
                            'option_id',
                            'eav_attribute_option',
                            'option_id'
                        ),
                        'option_id',
                        $installer->getTable('eav_attribute_option'),
                        'option_id',
                        Table::ACTION_CASCADE,
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_shopbybrand_brand_category',
                            'cat_id',
                            'mageplaza_shopbybrand_category',
                            'cat_id'
                        ),
                        'cat_id',
                        $installer->getTable('mageplaza_shopbybrand_category'),
                        'cat_id',
                        Table::ACTION_CASCADE,
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_shopbybrand_brand_category',
                            ['option_id', 'cat_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['option_id', 'cat_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Mageplaza Shopbybrand brand category table');

                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}
