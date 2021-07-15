<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Magento\Framework\App\ResourceConnection;

/**
 * Rates Resource Collection
 */
class StateValidator
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string|int $state
     * @param string|int $country
     *
     * @return bool
     */
    public function validateState($state, $country): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region'))
            ->where('region_id = ? OR default_name = ?', $state)
            ->where('country_id = ?', $country);

        return (bool)$connection->fetchOne($select);
    }
}
