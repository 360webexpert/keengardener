<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\ResourceModel;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class TableMaintainer
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $additionalTableSuffix = '_replica';

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (!isset($this->connection)) {
            $this->connection = $this->resource->getConnection();
        }

        return $this->connection;
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTable(string $tableName): string
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getReplicaTable(string $tableName): string
    {
        return $this->getTable($tableName) . $this->additionalTableSuffix;
    }

    /**
     * @param string $tableName
     */
    public function clearTable(string $tableName): void
    {
        if ($this->getConnection()->isTableExists($this->getTable($tableName))) {
            $this->getConnection()->truncateTable($this->getTable($tableName));
        }
    }

    /**
     * @param string $tableName
     * @param int $shippingMethodId
     */
    public function clearTableByMethodId(string $tableName, int $shippingMethodId): void
    {
        if ($this->getConnection()->isTableExists($this->getTable($tableName))) {
            $this->getConnection()->delete(
                $this->getTable($tableName),
                [ShippingTableRateInterface::METHOD_ID . ' = ? ' => $shippingMethodId]
            );
        }
    }

    /**
     * @param string $tableName
     * @param int $shippingMethodId
     * @return int
     */
    public function getRateCountByMethodId(string $tableName, int $shippingMethodId): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable($tableName), 'COUNT(*)')
            ->where(ShippingTableRateInterface::METHOD_ID . ' = ? ', $shippingMethodId);

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * @param string $tableName
     */
    public function copyDataToReplicaTable(string $tableName): void
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable($tableName));
        $this->connection->query(
            $this->connection->insertFromSelect(
                $select,
                $this->getReplicaTable($tableName),
                [],
                AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }
}
