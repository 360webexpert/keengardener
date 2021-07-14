<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Api\Data;

interface ShippingTableRateInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const COUNTRY = 'country';
    const STATE = 'state';
    const ZIP_FROM = 'zip_from';
    const ZIP_TO = 'zip_to';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    const WEIGHT_FROM = 'weight_from';
    const WEIGHT_TO = 'weight_to';
    const QTY_FROM = 'qty_from';
    const QTY_TO = 'qty_to';
    const SHIPPING_TYPE = 'shipping_type';
    const COST_BASE = 'cost_base';
    const COST_PERCENT = 'cost_percent';
    const COST_PRODUCT = 'cost_product';
    const COST_WEIGHT = 'cost_weight';
    const START_WEIGHT = 'start_weight';
    const TIME_DELIVERY = 'time_delivery';
    const NUM_ZIP_FROM = 'num_zip_from';
    const NUM_ZIP_TO = 'num_zip_to';
    const NAME_DELIVERY = 'name_delivery';
    const CITY = 'city';
    const METHOD_ID = 'method_id';

    /**
     * @return null|string
     */
    public function getCountry();

    /**
     * @param null|string $country
     *
     * @return ShippingTableRateInterface
     */
    public function setCountry($country);

    /**
     * @return null|string
     */
    public function getState();

    /**
     * @param null|string $state
     *
     * @return ShippingTableRateInterface
     */
    public function setState($state);

    /**
     * @return null|string
     */
    public function getZipFrom();

    /**
     * @param null|string $zipFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setZipFrom($zipFrom);

    /**
     * @return null|string
     */
    public function getZipTo();

    /**
     * @param null|string $zipTo
     *
     * @return ShippingTableRateInterface
     */
    public function setZipTo($zipTo);

    /**
     * @return null|string|float
     */
    public function getPriceFrom();

    /**
     * @param null|string|float $priceFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setPriceFrom($priceFrom);

    /**
     * @return null|string|float
     */
    public function getPriceTo();

    /**
     * @param null|string|float $priceTo
     *
     * @return ShippingTableRateInterface
     */
    public function setPriceTo($priceTo);

    /**
     * @return null|string|float
     */
    public function getWeightFrom();

    /**
     * @param null|string|float $weightFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setWeightFrom($weightFrom);

    /**
     * @return null|string|float
     */
    public function getWeightTo();

    /**
     * @param null|string|float $weightTo
     *
     * @return ShippingTableRateInterface
     */
    public function setWeightTo($weightTo);

    /**
     * @return null|string|float
     */
    public function getQtyFrom();

    /**
     * @param null|string|float $qtyFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setQtyFrom($qtyFrom);

    /**
     * @return null|string|float
     */
    public function getQtyTo();

    /**
     * @param null|string|float $qtyTo
     *
     * @return ShippingTableRateInterface
     */
    public function setQtyTo($qtyTo);

    /**
     * @return null|string
     */
    public function getShippingType();

    /**
     * @param null|string $shippingType
     *
     * @return ShippingTableRateInterface
     */
    public function setShippingType($shippingType);

    /**
     * @return string|float
     */
    public function getCostBase();

    /**
     * @param string|float $costBase
     *
     * @return ShippingTableRateInterface
     */
    public function setCostBase($costBase);

    /**
     * @return null|string|float
     */
    public function getCostPercent();

    /**
     * @param null|string|float $costPercent
     *
     * @return ShippingTableRateInterface
     */
    public function setCostPercent($costPercent);

    /**
     * @return null|string|float
     */
    public function getCostProduct();

    /**
     * @param null|string|float $costProduct
     *
     * @return ShippingTableRateInterface
     */
    public function setCostProduct($costProduct);

    /**
     * @return null|string|float
     */
    public function getCostWeight();

    /**
     * @param null|string|float $costWeight
     *
     * @return ShippingTableRateInterface
     */
    public function setCostWeight($costWeight);

    /**
     * @return null|string
     */
    public function getTimeDelivery();

    /**
     * @param null|string $timeDelivery
     *
     * @return ShippingTableRateInterface
     */
    public function setTimeDelivery($timeDelivery);

    /**
     * @return null|string
     */
    public function getCity();

    /**
     * @param null|string $city
     *
     * @return ShippingTableRateInterface
     */
    public function setCity($city);

    /**
     * @return null|string|int
     */
    public function getMethodId();

    /**
     * @param null|string|int $shippingMethodId
     *
     * @return ShippingTableRateInterface
     */
    public function setMethodId($shippingMethodId);
}
