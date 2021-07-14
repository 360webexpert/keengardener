<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Renderer;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Checks that row isn't already in DB by full match (all columns as a key)
 */
class ValidateUnique
{
    /**
     * @var string
     */
    private $mainTable = null;

    /**
     * @var QuoteValue
     */
    private $quoteValue;

    /**
     * @var Renderer
     */
    private $rateRenderer;

    /**
     * @var DescribeTable
     */
    private $describeTable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        QuoteValue $quoteValue,
        Renderer $rateRenderer,
        DescribeTable $describeTable,
        ResourceConnection $resourceConnection
    ) {
        $this->quoteValue = $quoteValue;
        $this->rateRenderer = $rateRenderer;
        $this->describeTable = $describeTable;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Is exist row in DB?
     * Can be called over 10k times per request.
     *
     * @param array $rowData where key is a column name
     *
     * @return bool
     */
    public function isRowExist(array $rowData): bool
    {
        $connection = $this->getConnection();

        //Raw query is more faster on big bunch of data
        //phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
        $select = 'SELECT 1 FROM `' . $this->getMainTable() . '` WHERE ';
        $select .= 'method_id=' . (int)$rowData[ShippingTableRateInterface::METHOD_ID];
        unset($rowData[ShippingTableRateInterface::METHOD_ID]);

        foreach ($rowData as $columnKey => $value) {
            $description = $this->describeTable->getColumnDescribe($columnKey);
            if ($description['IDENTITY']) {
                continue;
            }

            $value = $rowData[$columnKey] ?? $description['DEFAULT'];

            if ($value === null && $description['NULLABLE']) {
                $select .= ' AND `' . $columnKey . '` IS NULL';
                continue;
            }

            // because for users value in db null or '' are the same
            if ($value === '' && $description['NULLABLE'] && $description['DATA_TYPE'] === 'varchar') {
                $select .= ' AND (`' . $columnKey . '` IS NULL OR `'
                    . $columnKey . '` = ' . $this->quoteValue->quoteValue($columnKey, $value) . ')';
                continue;
            }

            $value = $this->quoteValue->quoteValue($columnKey, $value);

            $select .= ' AND `' . $columnKey . '` = ' . $value;
        }

        $select .= ' LIMIT 1';
        //phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
        $select = 'SELECT EXISTS (' . $select . ')';

        return (bool)$connection->fetchOne($select);
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return string
     */
    private function getMainTable(): string
    {
        if ($this->mainTable === null) {
            $this->mainTable = $this->resourceConnection->getTableName(RateResource::MAIN_TABLE);
        }

        return $this->mainTable;
    }
}
