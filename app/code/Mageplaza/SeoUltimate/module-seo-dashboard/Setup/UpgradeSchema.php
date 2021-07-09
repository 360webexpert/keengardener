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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\SeoDashboard\Setup
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

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->dropTable($installer);

            if (!$installer->tableExists('mageplaza_seodashboard_report_issue')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_seodashboard_report_issue'))
                    ->addColumn('issue_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'Data Id')
                    ->addColumn('issue_type', Table::TYPE_INTEGER, null, ['nullable => false'], 'Issue Type')
                    ->addColumn('entity_ids', Table::TYPE_TEXT, 256, ['nullable => false'], 'Entity Ids')
                    ->addColumn('fields', Table::TYPE_INTEGER, null, ['nullable => false'], 'Fields')
                    ->addColumn('entity', Table::TYPE_TEXT, 64, ['nullable => false'], 'Entity')
                    ->addColumn('store', Table::TYPE_INTEGER, null, ['nullable => false'], 'Store Id')
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
                    ->setComment('Seo Report Issue Table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_seodashboard_low_count_report_issue')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_seodashboard_low_count_report_issue'))
                    ->addColumn('issue_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'Data Id')
                    ->addColumn('entity_ids', Table::TYPE_TEXT, 256, ['nullable => false'], 'Entity Ids')
                    ->addColumn('fields', Table::TYPE_INTEGER, null, ['nullable => false'], 'Fields')
                    ->addColumn('count', Table::TYPE_INTEGER, null, ['nullable => false'], 'Count')
                    ->addColumn('entity', Table::TYPE_TEXT, 64, ['nullable => false'], 'Entity')
                    ->addColumn('store', Table::TYPE_INTEGER, null, ['nullable => false'], 'Store Id')
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
                    ->setComment('Seo Report Issue Table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_seodashboard_noroute_report_issue')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_seodashboard_noroute_report_issue'))
                    ->addColumn('issue_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'Data Id')
                    ->addColumn('uri', Table::TYPE_TEXT, 256, ['nullable => false'], 'Uri')
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
                    ->setComment('Seo Report Issue Table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_seodashboard_mediate_content_data')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_seodashboard_mediate_content_data'))
                    ->addColumn('data_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'Issue Id')
                    ->addColumn('entity_id', Table::TYPE_INTEGER, null, ['nullable => false'], 'Entity Id')
                    ->addColumn('frontend_identity', Table::TYPE_TEXT, 256, ['nullable => true'], 'Frontend Identify')
                    ->addColumn('meta_title', Table::TYPE_TEXT, 256, ['nullable => true'], 'Meta Title')
                    ->addColumn('meta_description', Table::TYPE_TEXT, 256, ['nullable => true'], 'Meta Description')
                    ->addColumn('description', Table::TYPE_TEXT, 256, ['nullable => true'], 'Description')
                    ->addColumn('short_description', Table::TYPE_TEXT, 256, ['nullable => true'], 'Short Description')
                    ->addColumn('entity', Table::TYPE_TEXT, 64, ['nullable => false'], 'Entity')
                    ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable => false'], 'Store Id')
                    ->setComment('Duplicate Meta Content Data Table');

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $connection = $installer->getConnection();

            $connection->dropTable($installer->getTable('mageplaza_seodashboard_issue_type'));

            $mediateTable = $installer->getTable('mageplaza_seodashboard_mediate_content_data');
            $connection->addIndex(
                $mediateTable,
                $installer->getIdxName('mageplaza_seodashboard_mediate_content_data', ['entity_id']),
                ['entity_id']
            );
            $connection->addIndex(
                $mediateTable,
                $installer->getIdxName('mageplaza_seodashboard_mediate_content_data', ['store_id']),
                ['store_id']
            );

            $lowCountIssueTable = $installer->getTable('mageplaza_seodashboard_low_count_report_issue');
            $connection->addIndex(
                $lowCountIssueTable,
                $installer->getIdxName('mageplaza_seodashboard_low_count_report_issue', ['fields']),
                ['fields']
            );
            $connection->addIndex(
                $lowCountIssueTable,
                $installer->getIdxName('mageplaza_seodashboard_low_count_report_issue', ['store']),
                ['store']
            );

            $lowCountIssueTable = $installer->getTable('mageplaza_seodashboard_report_issue');
            $connection->addIndex(
                $lowCountIssueTable,
                $installer->getIdxName('mageplaza_seodashboard_report_issue', ['issue_type']),
                ['issue_type']
            );
            $connection->addIndex(
                $lowCountIssueTable,
                $installer->getIdxName('mageplaza_seodashboard_report_issue', ['fields']),
                ['fields']
            );
            $connection->addIndex(
                $lowCountIssueTable,
                $installer->getIdxName('mageplaza_seodashboard_report_issue', ['store']),
                ['store']
            );
        }

        $installer->endSetup();
    }

    /**
     * Drop table
     *
     * @param $installer
     */
    public function dropTable($installer)
    {
        $tables = [
            'mageplaza_seodashboard_report_issue',
            'mageplaza_seodashboard_issue_type',
            'mageplaza_seodashboard_low_count_report_issue',
            'mageplaza_seodashboard_noroute_report_issue',
            'mageplaza_seodashboard_mediate_content_data'
        ];

        foreach ($tables as $table) {
            if ($installer->tableExists($table)) {
                $installer->getConnection()->dropTable($installer->getTable($table));
            }
        }
    }
}
