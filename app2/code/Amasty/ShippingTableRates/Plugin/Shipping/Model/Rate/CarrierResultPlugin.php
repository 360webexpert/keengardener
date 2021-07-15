<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Plugin\Shipping\Model\Rate;

class CarrierResultPlugin
{
    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $previousRate
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $nextRate
     *
     * @return int
     */
    private function sortRates($previousRate, $nextRate)
    {
        if ($previousRate->getSortOrder() == $nextRate->getSortOrder()) {
            return $previousRate->getPrice() <=> $nextRate->getPrice();
        }

        return ($previousRate->getSortOrder() < $nextRate->getSortOrder()) ? -1 : 1;
    }

    /**
     * Sort TableRates by sort_order
     *
     * @param \Magento\Shipping\Model\Rate\CarrierResult $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetAllRates(\Magento\Shipping\Model\Rate\Result $subject, $result)
    {
        $amrates = [];
        $newResult = [];

        foreach ($result as $rate) {
            if ($rate->getCarrier() == 'amstrates') {
                $amrates[] = $rate;
            } else {
                $newResult[] = $rate;
            }
        }

        if (count($amrates) > 1) {
            usort($amrates, [$this, 'sortRates']);
        }

        $res = array_merge($newResult, $amrates);

        return $res;
    }
}
