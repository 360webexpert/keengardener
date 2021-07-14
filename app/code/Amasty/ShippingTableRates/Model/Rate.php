<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Rate Data of Shipping Method.
 *  Shipping Method can have set of Rates
 */
class Rate extends AbstractModel implements ShippingTableRateInterface
{
    const ALGORITHM_SUM = 0;
    const ALGORITHM_MAX = 1;
    const ALGORITHM_MIN = 2;
    const MAX_VALUE = 99999999;
    const WEIGHT_TYPE_VOLUMETRIC = 1;
    const WEIGHT_TYPE_WEIGHT = 2;
    const WEIGHT_TYPE_MAX = 3;
    const WEIGHT_TYPE_MIN = 4;
    const ALL_VALUE = 0;

    protected function _construct()
    {
        $this->_init(RateResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getMethodId()
    {
        return $this->_getData(ShippingTableRateInterface::METHOD_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMethodId($methodId)
    {
        $this->setData(ShippingTableRateInterface::METHOD_ID, $methodId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->_getData(ShippingTableRateInterface::COUNTRY);
    }

    /**
     * @inheritdoc
     */
    public function setCountry($country)
    {
        $this->setData(ShippingTableRateInterface::COUNTRY, $country);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->_getData(ShippingTableRateInterface::STATE);
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->setData(ShippingTableRateInterface::STATE, $state);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getZipFrom()
    {
        return $this->_getData(ShippingTableRateInterface::ZIP_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setZipFrom($zipFrom)
    {
        $this->setData(ShippingTableRateInterface::ZIP_FROM, $zipFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getZipTo()
    {
        return $this->_getData(ShippingTableRateInterface::ZIP_TO);
    }

    /**
     * @inheritdoc
     */
    public function setZipTo($zipTo)
    {
        $this->setData(ShippingTableRateInterface::ZIP_TO, $zipTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceFrom()
    {
        return $this->_getData(ShippingTableRateInterface::PRICE_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setPriceFrom($priceFrom)
    {
        $this->setData(ShippingTableRateInterface::PRICE_FROM, $priceFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceTo()
    {
        return $this->_getData(ShippingTableRateInterface::PRICE_TO);
    }

    /**
     * @inheritdoc
     */
    public function setPriceTo($priceTo)
    {
        $this->setData(ShippingTableRateInterface::PRICE_TO, $priceTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeightFrom()
    {
        return $this->_getData(ShippingTableRateInterface::WEIGHT_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setWeightFrom($weightFrom)
    {
        $this->setData(ShippingTableRateInterface::WEIGHT_FROM, $weightFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeightTo()
    {
        return $this->_getData(ShippingTableRateInterface::WEIGHT_TO);
    }

    /**
     * @inheritdoc
     */
    public function setWeightTo($weightTo)
    {
        $this->setData(ShippingTableRateInterface::WEIGHT_TO, $weightTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQtyFrom()
    {
        return $this->_getData(ShippingTableRateInterface::QTY_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setQtyFrom($qtyFrom)
    {
        $this->setData(ShippingTableRateInterface::QTY_FROM, $qtyFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQtyTo()
    {
        return $this->_getData(ShippingTableRateInterface::QTY_TO);
    }

    /**
     * @inheritdoc
     */
    public function setQtyTo($qtyTo)
    {
        $this->setData(ShippingTableRateInterface::QTY_TO, $qtyTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippingType()
    {
        return $this->_getData(ShippingTableRateInterface::SHIPPING_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setShippingType($shippingType)
    {
        $this->setData(ShippingTableRateInterface::SHIPPING_TYPE, $shippingType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostBase()
    {
        return $this->_getData(ShippingTableRateInterface::COST_BASE);
    }

    /**
     * @inheritdoc
     */
    public function setCostBase($costBase)
    {
        $this->setData(ShippingTableRateInterface::COST_BASE, $costBase);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostPercent()
    {
        return $this->_getData(ShippingTableRateInterface::COST_PERCENT);
    }

    /**
     * @inheritdoc
     */
    public function setCostPercent($costPercent)
    {
        $this->setData(ShippingTableRateInterface::COST_PERCENT, $costPercent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostProduct()
    {
        return $this->_getData(ShippingTableRateInterface::COST_PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function setCostProduct($costProduct)
    {
        $this->setData(ShippingTableRateInterface::COST_PRODUCT, $costProduct);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostWeight()
    {
        return $this->_getData(ShippingTableRateInterface::COST_WEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function setCostWeight($costWeight)
    {
        $this->setData(ShippingTableRateInterface::COST_WEIGHT, $costWeight);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTimeDelivery()
    {
        return $this->_getData(ShippingTableRateInterface::TIME_DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setTimeDelivery($timeDelivery)
    {
        $this->setData(ShippingTableRateInterface::TIME_DELIVERY, $timeDelivery);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumZipFrom()
    {
        return $this->_getData(ShippingTableRateInterface::NUM_ZIP_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setNumZipFrom($numZipFrom)
    {
        $this->setData(ShippingTableRateInterface::NUM_ZIP_FROM, $numZipFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumZipTo()
    {
        return $this->_getData(ShippingTableRateInterface::NUM_ZIP_TO);
    }

    /**
     * @inheritdoc
     */
    public function setNumZipTo($numZipTo)
    {
        $this->setData(ShippingTableRateInterface::NUM_ZIP_TO, $numZipTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->_getData(ShippingTableRateInterface::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->setData(ShippingTableRateInterface::CITY, $city);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNameDelivery()
    {
        return $this->_getData(ShippingTableRateInterface::NAME_DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setNameDelivery($nameDelivery)
    {
        $this->setData(ShippingTableRateInterface::NAME_DELIVERY, $nameDelivery);

        return $this;
    }
}
