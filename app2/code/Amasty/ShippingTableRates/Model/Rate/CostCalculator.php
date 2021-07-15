<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Rate;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\ConfigProvider;
use Amasty\ShippingTableRates\Model\Method;
use Amasty\ShippingTableRates\Model\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection as MethodCollection;
use Magento\Quote\Model\Quote\Address\RateRequest;

class CostCalculator
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $shippingFlatParams = [
        ShippingTableRateInterface::COUNTRY,
        ShippingTableRateInterface::CITY
    ];

    /**
     * @var array
     */
    private $shippingRangeParams = [
        'price',
        'qty',
        'weight'
    ];

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @TODO: very complex method, need to split and cover by tests
     * @param RateRequest $request
     * @param MethodCollection $collection
     * @param array $allRates
     * @param array $totals
     * @return array
     */
    public function calculateCosts(
        RateRequest $request,
        MethodCollection $collection,
        array $allRates,
        array $totals
    ): array {
        $minEmptyValuesCount = 0;
        $result = [];
        $minRates = $collection->hashMinRate();
        $maxRates = $collection->hashMaxRate();

        foreach ($allRates as $rate) {
            $emptyValuesCount = $this->calculateEmptyValues($rate);
            $methodId = (int)$rate['method_id'];

            $totals['not_free_weight'] = $this->calculateNotFreeWeight(
                (float)$totals['not_free_weight'],
                (float)$rate['start_weight']
            );
            
            if ($this->canCalculateCost($totals)) {
                $cost = $rate['cost_base'] + ($totals['not_free_price'] * $rate['cost_percent'] / 100)
                    + ($totals['not_free_qty'] * $rate['cost_product'])
                    + ($totals['not_free_weight'] * $rate['cost_weight']);
            } else {
                $cost = 0.0;
            }

            if ((empty($minEmptyValuesCount) && empty($result))
                || $this->isPrioritizedRate($rate, $result, $minEmptyValuesCount, $emptyValuesCount, $cost)
            ) {
                $minEmptyValuesCount = $emptyValuesCount;
                $result['cost'] = $this->includeMinMaxRates((float)$cost, $methodId, $maxRates, $minRates);
                $result['time'] = $rate['time_delivery'];
                $result['shipping_type'] = $rate['shipping_type'];
                $result['name_delivery'] = $rate['name_delivery'];
                $result['city'] = $rate['city'];
                $result['country'] = $rate['country'];
                $result['state'] = $rate['state'];
            }
        }

        return $this->applyFreeShipping($result, $request->getAllItems());
    }

    /**
     * @param Method $method
     * @param array $allCosts
     * @param array $cost
     * @return array
     */
    public function setCostTime(Method $method, array $allCosts, array $cost): array
    {
        $methodId = $method->getId();

        switch ($method->getSelectRate()) {
            case Rate::ALGORITHM_MAX:
                if ($allCosts[$methodId]['cost'] < $cost['cost']) {
                    $allCosts[$methodId]['cost'] = $cost['cost'];
                    $allCosts[$methodId]['time'] = $cost['time'];
                    $allCosts[$methodId]['name_delivery'] = $cost['name_delivery'];
                }
                break;
            case Rate::ALGORITHM_MIN:
                if ($allCosts[$methodId]['cost'] > $cost['cost']) {
                    $allCosts[$methodId]['cost'] = $cost['cost'];
                    $allCosts[$methodId]['time'] = $cost['time'];
                    $allCosts[$methodId]['name_delivery'] = $cost['name_delivery'];
                }
                break;
            default:
                $allCosts[$methodId]['cost'] += $cost['cost'];
                $allCosts[$methodId]['name_delivery'] = '';

                if ($cost['time'] > $allCosts[$methodId]['time']) {
                    $allCosts[$methodId]['time'] = $cost['time'];
                }
        }

        return $allCosts;
    }

    /**
     * Calculate empty values in rate data to prioritize rates
     *
     * @param array $rate
     * @return int
     */
    private function calculateEmptyValues(array $rate): int
    {
        $emptyValuesCount = 0;

        if (empty($rate[ShippingTableRateInterface::SHIPPING_TYPE])) {
            $emptyValuesCount++;
        }

        foreach ($this->shippingFlatParams as $param) {
            if (empty($rate[$param])) {
                $emptyValuesCount++;
            }
        }

        foreach ($this->shippingRangeParams as $param) {
            if ((ceil($rate[$param . '_from']) == 0) && (ceil($rate[$param . '_to']) == Rate::MAX_VALUE)) {
                $emptyValuesCount++;
            }
        }

        if (empty($rate[ShippingTableRateInterface::ZIP_FROM]) && empty($rate[ShippingTableRateInterface::ZIP_TO])) {
            $emptyValuesCount++;
        }

        return $emptyValuesCount;
    }

    /**
     * @param array $totals
     * @return bool
     */
    private function canCalculateCost(array $totals): bool
    {
        if ((!$totals['not_free_price'] && !$totals['not_free_qty']
            && (!$totals['not_free_weight'] || !$totals['volumetric']))
            || !$totals['not_free_qty']
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param float $cost
     * @param int $methodId
     * @param array $maxRates
     * @param array $minRates
     * @return float
     */
    private function includeMinMaxRates(float $cost, int $methodId, array $maxRates, array $minRates): float
    {
        if ($maxRates[$methodId] != '0.00' && $maxRates[$methodId] < $cost) {
            $cost = $maxRates[$methodId];
        }

        if ($minRates[$methodId] != '0.00' && $minRates[$methodId] > $cost) {
            $cost = $minRates[$methodId];
        }

        return (float)$cost;
    }

    /**
     * @param array $allCosts
     * @param array $items
     * @return array
     */
    private function applyFreeShipping(array $allCosts, array $items): array
    {
        if (!empty($allCosts) && $this->isFreeShipping($items)) {
            $allCosts['cost'] = 0.0;
        }

        return $allCosts;
    }

    /**
     * @param array $items
     * @return bool
     */
    private function isFreeShipping(array $items): bool
    {
        $allowFreePromo = $this->configProvider->isPromoAllowed();
        $isFreeShipping = false;

        foreach ($items as $item) {
            $address = $item->getAddress();

            if ($allowFreePromo && $address->getFreeShipping() === true) {
                $isFreeShipping = true;
            }
        }

        return $isFreeShipping;
    }

    /**
     * @param array $rate
     * @param array $result
     * @param int $minEmptyValuesCount
     * @param int $emptyValuesCount
     * @param float $cost
     * @return bool
     */
    private function isPrioritizedRate(
        array $rate,
        array $result,
        int $minEmptyValuesCount,
        int $emptyValuesCount,
        float $cost
    ): bool {

        // Checking for a more precisely described rate
        if ((($minEmptyValuesCount > $emptyValuesCount
                || ($minEmptyValuesCount == $emptyValuesCount && $cost > $result['cost']))
            && ($rate['shipping_type'] != 0 || $result['shipping_type'] == 0))
        ) {
            return true;
        }

        // Checking for a more precisely described rate for City field
        if (($rate['city'] != $result['city']
            && ($rate['country'] == $result['country']
                && $rate['state'] == $result['state']))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param float $notFreeWeight
     * @param float $startWeight
     *
     * @return float
     */
    private function calculateNotFreeWeight(float $notFreeWeight, float $startWeight): float
    {
        if ($notFreeWeight >= $startWeight) {
            $notFreeWeight -= $startWeight;
        } else {
            $notFreeWeight = 0.0;
        }
        
        return $notFreeWeight;
    }
}
