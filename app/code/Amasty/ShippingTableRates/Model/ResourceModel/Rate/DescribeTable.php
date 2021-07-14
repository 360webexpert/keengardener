<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class DescribeTable
{
    /**
     * @var string
     */
    private $mainTable = null;

    /**
     * @var array
     */
    private $tableDescribe = null;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get columns mysql describe
     *
     * @return array
     */
    public function getDescribe(): array
    {
        if ($this->tableDescribe === null) {
            $this->tableDescribe = $this->getConnection()->describeTable($this->getMainTable());
            unset($this->tableDescribe[ShippingTableRateInterface::ID]);
            unset($this->tableDescribe[ShippingTableRateInterface::METHOD_ID]);
        }

        return $this->tableDescribe;
    }

    /**
     * @param string $columnName
     * @return array|null
     */
    public function getColumnDescribe(string $columnName): ?array
    {
        $tableDescribe = $this->getDescribe();

        return $tableDescribe[$columnName] ?? null;
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
