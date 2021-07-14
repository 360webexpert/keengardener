<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */

declare(strict_types=1);

namespace Amasty\ShippingTableRates\Model\Rate;

use Amasty\ShippingTableRates\Model\ConfigProvider;
use Amasty\ShippingTableRates\Model\Method;
use Amasty\ShippingTableRates\Model\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection as MethodCollection;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

class Provider
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RateResource
     */
    private $rateResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ItemsTotalCalculator
     */
    private $itemsTotalCalculator;

    /**
     * @var CostCalculator
     */
    private $costCalculator;

    /**
     * @var ItemValidator
     */
    private $itemValidator;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RateResource $rateResource,
        ConfigProvider $configProvider,
        ItemsTotalCalculator $itemsTotalCalculator,
        CostCalculator $costCalculator,
        ItemValidator $itemValidator
    ) {
        $this->productRepository = $productRepository;
        $this->rateResource = $rateResource;
        $this->configProvider = $configProvider;
        $this->itemsTotalCalculator = $itemsTotalCalculator;
        $this->costCalculator = $costCalculator;
        $this->itemValidator = $itemValidator;
    }

    /**
     * @param RateRequest $request
     * @param MethodCollection $collection
     *
     * @return array
     */
    public function getRates(RateRequest $request, MethodCollection $collection): array
    {
        if (!$request->getAllItems() || !$collection->getSize()) {
            return [];
        }

        $methodIds = [];
        foreach ($collection as $method) {
            $methodIds[] = $method->getId();
        }

        $itemsShippingTypes = $this->getShippingTypes($request);
        $shippingTypes = array_merge($itemsShippingTypes, [Rate::ALL_VALUE]);
        $rateTypes = $this->rateResource->getUniqueRateTypes($methodIds, $shippingTypes);
        $cleanTotals = $this->itemsTotalCalculator->execute($request, Rate::ALL_VALUE);

        $allCosts = [];
        $freeTypes = [];
        $collectedTypes = [];

        foreach ($rateTypes as $methodId => $methodShippingTypes) {
            /** @var Method $method */
            $method = $collection->getItemById($methodId);
            $freeTypes[$methodId] = $method->getFreeTypes();
            $allTotals = $cleanTotals;

            foreach ($methodShippingTypes as $shippingType) {
                if ($shippingType !== Rate::ALL_VALUE) {
                    $totals = $this->itemsTotalCalculator->execute($request, $shippingType);
                } else {
                    $totals = $allTotals;
                }

                if (!($totals['not_free_qty'] > 0) && !($totals['qty'] > 0)) {
                    continue;
                }

                if ($allTotals['qty'] > 0
                    && (!$this->configProvider->getDontSplit() || $allTotals['qty'] === $totals['qty'])
                ) {
                    $totals['not_free_weight'] = $this->getWeightForUse($method, $totals);
                    $allTotals = $this->changeAllTotalsCapacity($allTotals, $totals);
                    $ratesData = $this->rateResource->getMethodRates(
                        $request,
                        $methodId,
                        $totals,
                        $shippingType,
                        $this->configProvider->isPromoAllowed()
                    );
                    $calculatedCost = $this->costCalculator->calculateCosts($request, $collection, $ratesData, $totals);

                    if (empty($calculatedCost)) {
                        continue;
                    }

                    if (empty($allCosts[$methodId])) {
                        $allCosts[$methodId]['cost'] = $calculatedCost['cost'];
                        $allCosts[$methodId]['time'] = $calculatedCost['time'];
                        $allCosts[$methodId]['name_delivery'] = $calculatedCost['name_delivery'];
                    } else {
                        $allCosts = $this->costCalculator->setCostTime($method, $allCosts, $calculatedCost);
                    }
                    $collectedTypes[$methodId][] = $shippingType;
                }
            }
        }

        return $this->unsetUnnecessaryCosts($allCosts, $itemsShippingTypes, $collectedTypes, $freeTypes);
    }

    /**
     * @param array $allCosts
     * @param array $shippingTypes
     * @param array $collectedTypes
     * @param array $freeTypes
     * @return array
     */
    private function unsetUnnecessaryCosts(
        array $allCosts,
        array $shippingTypes,
        array $collectedTypes,
        array $freeTypes
    ): array {
        //do not show method if quote has "unsuitable" items
        foreach ($allCosts as $key => $cost) {
            //1.if the method contains rate with type == All
            if (in_array(Rate::ALL_VALUE, $collectedTypes[$key])) {
                continue;
            }
            //2.if the method rates contain types for every items in quote
            $extraTypes = array_diff($shippingTypes, $collectedTypes[$key]);
            if (!$extraTypes) {
                continue;
            }
            //3.if the method free types contain types for every item didn't pass (2)
            if (!array_diff($extraTypes, $freeTypes[$key])) {
                continue;
            }

            //else — do not show the method;
            unset($allCosts[$key]);
        }

        return $allCosts;
    }

    /**
     * @param RateRequest $request
     * @return array
     */
    private function getShippingTypes(RateRequest $request): array
    {
        $shippingTypes = [];

        /** @var Item $item */
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if ($this->itemValidator->isShouldProcessChildren($item)) {
                foreach ($item->getChildren() as $child) {
                    $productId = $child->getProductId();
                }
            } else {
                $productId = $item->getProductId();
            }

            $product = $this->productRepository->getById($productId);

            if ($product->getAmShippingType()) {
                $shippingTypes[] = $product->getAmShippingType();
            } else {
                $shippingTypes[] = Rate::ALL_VALUE;
            }
        }

        return array_unique($shippingTypes);
    }

    /**
     * Change all totals capacity for method by rate totals
     *
     * @param array $allTotals
     * @param array $currentTotals
     * @return array
     */
    private function changeAllTotalsCapacity(array $allTotals, array $currentTotals): array
    {
        /**
         * avoid php opcache 7.0.33 bug
         */
        $allTotals['not_free_price'] = $allTotals['not_free_price'] - $currentTotals['not_free_price'];
        $allTotals['not_free_weight'] = $allTotals['not_free_weight'] - $currentTotals['not_free_weight'];
        $allTotals['not_free_volumetric'] =
            $allTotals['not_free_volumetric'] - $currentTotals['not_free_volumetric'];
        $allTotals['not_free_qty'] = $allTotals['not_free_qty'] - $currentTotals['not_free_qty'];
        $allTotals['qty'] = $allTotals['qty'] - $currentTotals['qty'];

        return $allTotals;
    }

    /**
     * @param Method $method
     * @param array $totals
     * @return float
     */
    private function getWeightForUse(Method $method, array $totals): float
    {
        switch ($method->getWeightType()) {
            case Rate::WEIGHT_TYPE_WEIGHT:
                return (float)$totals['not_free_weight'];
            case Rate::WEIGHT_TYPE_VOLUMETRIC:
                return (float)$totals['not_free_volumetric'];
            case Rate::WEIGHT_TYPE_MIN:
                return (float)min($totals['not_free_volumetric'], $totals['not_free_weight']);
            default: // Rate::WEIGHT_TYPE_MAX
                return (float)max($totals['not_free_volumetric'], $totals['not_free_weight']);
        }
    }
}
