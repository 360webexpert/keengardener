<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Api;

use Amasty\ShippingTableRates\Api\Data\RateInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

interface RateRepositoryInterface
{
    /**
     * @param RateInterface $rate
     *
     * @return RateInterface
     */
    public function save(RateInterface $rate);

    /**
     * @param RateInterface $rate
     *
     * @return bool true on success
     *
     * @throws CouldNotDeleteException
     */
    public function delete(RateInterface $rate);
}
