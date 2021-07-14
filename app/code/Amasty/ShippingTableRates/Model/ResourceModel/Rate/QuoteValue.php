<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class QuoteValue
{
    /**
     * @var DescribeTable
     */
    private $describeTable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(DescribeTable $describeTable, ResourceConnection $resourceConnection)
    {
        $this->describeTable = $describeTable;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $columnKey
     * @param mixed $value
     *
     * @return float|int|string
     */
    public function quoteValue(string $columnKey, $value)
    {
        $description = $this->describeTable->getColumnDescribe($columnKey);

        if ($description === null) {
            return $this->getConnection()->quote($value);
        }

        switch ($description['DATA_TYPE']) {
            case 'decimal':
            case 'numeric':
            case 'float':
                $scale = (int)$description['SCALE'] ?? 2;

                $value = (float)$value;
                $value = number_format($value, $scale, '.', '');
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                $value = (int)$value;
                break;
            default:
                $value = $this->getConnection()->quote($value);
        }

        return $value;
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
