<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Amasty\ShippingTableRates\Api\RateRepositoryInterface;
use Amasty\ShippingTableRates\Api\Data\RateInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class RateRepository implements RateRepositoryInterface
{
    /**
     * @var RateResource
     */
    private $rateResource;

    public function __construct(
        RateResource $rateResource
    ) {
        $this->rateResource = $rateResource;
    }

    /**
     * @param RateInterface $rate
     * @return RateInterface
     * @throws CouldNotSaveException
     */
    public function save(RateInterface $rate)
    {
        try {
            $this->rateResource->save($rate);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save new rate. Error: %1', $e->getMessage()));
        }

        return $rate;
    }

    /**
     * @param RateInterface $rate
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RateInterface $rate)
    {
        try {
            $this->rateResource->delete($rate);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove rate. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
