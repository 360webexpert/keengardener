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

class DeleteQueryCollector
{
    /**
     * @var string
     */
    private $mainTable = null;

    /**
     * @var int
     */
    private $methodId = null;

    /**
     * @var array
     */
    private $bunchRowsValues = [];

    /**
     * @var int
     */
    private $deletedRowsCount = 0;

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
     * @param array $rowData
     */
    public function collectRowsBunch(array $rowData): void
    {
        if (!$this->methodId) {
            $this->methodId = (int)$rowData[ShippingTableRateInterface::METHOD_ID];
        }
        $this->bunchRowsValues[] = $this->prepareCompareRowsQuery($rowData);
    }

    public function deleteBunch(): void
    {
        if ($this->bunchRowsValues) {
            $this->deleteBunchQuery();
        }

        $this->bunchRowsValues = [];
    }

    /**
     * @param array $rowData
     * @return string
     */
    private function prepareCompareRowsQuery(array $rowData) : string
    {
        $select = '';
        $condition = '';
        unset($rowData[ShippingTableRateInterface::METHOD_ID]);

        foreach ($rowData as $columnKey => $value) {
            $description = $this->describeTable->getColumnDescribe($columnKey);
            if ($description === null) {
                continue;
            }

            if ($value === null && $description['NULLABLE']) {
                $select .= $condition . '`' . $columnKey . '` IS NULL';
                $condition = ' AND ';
                continue;
            }

            // because for users value in db null or '' are the same
            if ($value === '' && $description['NULLABLE'] && $description['DATA_TYPE'] === 'varchar') {
                $select .= $condition . '(`' . $columnKey . '` IS NULL OR `'
                    . $columnKey . '` = ' . $this->quoteValue->quoteValue($columnKey, $value) . ')';
                $condition = ' AND ';
                continue;
            }

            $value = $this->quoteValue->quoteValue($columnKey, $value);

            $select .= $condition . '`' . $columnKey . '` = ' . $value;
            $condition = ' AND ';
        }

        return $select;
    }

    private function deleteBunchQuery(): void
    {
        $connection = $this->getConnection();

        //phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
        $select = 'DELETE FROM `' . $this->getMainTable() . '` WHERE '
            . '`method_id` = ' . $this->methodId . ' AND '
            . ' ((' . implode((') OR ('), $this->bunchRowsValues) . '))';

        $stmt = $connection->query($select);
        $this->deletedRowsCount = (int)$stmt->rowCount();
    }

    /**
     * @return int
     */
    public function getDeletedRowsCount(): int
    {
        return $this->deletedRowsCount;
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
