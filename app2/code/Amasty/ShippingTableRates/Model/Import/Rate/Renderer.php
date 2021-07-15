<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Import\Rate;

use Amasty\ShippingTableRates\Helper\Data as HelperData;
use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;

class Renderer
{
    /**
     * max value for numeric variables
     */
    const MAX_NUMERIC_VALUE = 99999999;

    /**
     * @var HelperData
     */
    private $helper;

    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param array $rateData
     *
     * @return array
     */
    public function renderRateData($rateData)
    {
        $rateData[ShippingTableRateInterface::COUNTRY]
            = $this->renderCountry($rateData[ShippingTableRateInterface::COUNTRY]);

        $rateData[ShippingTableRateInterface::STATE] = $this->renderState(
            $rateData[ShippingTableRateInterface::STATE],
            $rateData[ShippingTableRateInterface::COUNTRY]
        );

        $rateData[ShippingTableRateInterface::SHIPPING_TYPE]
            = $this->renderShippingType($rateData[ShippingTableRateInterface::SHIPPING_TYPE]);

        if (!isset($rateData[ShippingTableRateInterface::PRICE_TO])) {
            $rateData[ShippingTableRateInterface::PRICE_TO] = self::MAX_NUMERIC_VALUE;
        }

        if (!isset($rateData[ShippingTableRateInterface::WEIGHT_TO])) {
            $rateData[ShippingTableRateInterface::WEIGHT_TO] = self::MAX_NUMERIC_VALUE;
        }

        if (!isset($rateData[ShippingTableRateInterface::QTY_TO])) {
            $rateData[ShippingTableRateInterface::QTY_TO] = self::MAX_NUMERIC_VALUE;
        }

        $rateData[ShippingTableRateInterface::NUM_ZIP_FROM]
            = $this->helper->getDataFromZip($rateData[ShippingTableRateInterface::ZIP_FROM])['district'];

        $rateData[ShippingTableRateInterface::NUM_ZIP_TO]
            = $this->helper->getDataFromZip($rateData[ShippingTableRateInterface::ZIP_TO])['district'];

        $rateData[ShippingTableRateInterface::ZIP_FROM] = (string)$rateData[ShippingTableRateInterface::ZIP_FROM];

        $rateData[ShippingTableRateInterface::ZIP_TO] = (string)$rateData[ShippingTableRateInterface::ZIP_TO];

        $rateData[ShippingTableRateInterface::CITY] = (string)$rateData[ShippingTableRateInterface::CITY];

        $rateData[ShippingTableRateInterface::NAME_DELIVERY]
            = (string)$rateData[ShippingTableRateInterface::NAME_DELIVERY];

        $rateData[ShippingTableRateInterface::TIME_DELIVERY]
            = (string)$rateData[ShippingTableRateInterface::TIME_DELIVERY];

        return $rateData;
    }

    /**
     * @param null|string $country
     *
     * @return string
     */
    public function renderCountry($country)
    {
        if (!$country || $country == 'All') {
            return Mapping::COUNTRY_CODE_ALL;
        }

        $countryNames = $this->helper->getCountriesHash();

        return array_key_exists($country, $countryNames) ? $country : array_search($country, $countryNames);
    }

    /**
     * @param null|string|int $state
     * @param null|string|int $country
     *
     * @return string|int
     */
    public function renderState($state, $country)
    {
        if (!$state || $state == 'All') {
            return Mapping::STATE_CODE_ALL;
        }

        $stateNames = $this->helper->getStatesHash();

        return array_key_exists($state, $stateNames) ? $state : array_search($state, $stateNames);
    }

    /**
     * @param null|string|int $shippingType
     *
     * @return string|int
     */
    public function renderShippingType($shippingType)
    {
        if (!$shippingType || $shippingType == 'All') {
            return Mapping::SHIPPING_TYPE_ALL;
        }

        $typeLabels = $this->helper->getTypesHash();

        return isset($typeLabels[$shippingType]) ? $shippingType : array_search($shippingType, $typeLabels);
    }
}
