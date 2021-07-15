<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Escaper;

/**
 * @deprecated should be moved to separated classes
 * phpcs:ignoreFile
 */
class Data
{
    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var array
     */
    private $countries = [];

    /**
     * @var array
     */
    private $states = [];

    /**
     * @var array
     */
    private $types = [];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    public function __construct(
        EavConfig $eavConfig,
        Escaper $escaper,
        CountryCollectionFactory $countryCollectionFactory,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->escaper = $escaper;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        $countries = $this->countryCollectionFactory->create()->toOptionArray();
        unset($countries[0]);

        return $countries;
    }

    /**
     * @return array
     */
    public function getCountriesHash(): array
    {
        if ($this->countries) {
            return $this->countries;
        }

        $this->countries = $this->toHash($this->getCountries());

        return $this->countries;
    }

    /**
     * @return array
     */
    public function getStates()
    {
        $states = $this->regionCollectionFactory->create()->toOptionArray();
        $states = $this->addCountriesToStates($states);

        return $states;
    }

    /**
     * @return array
     */
    public function getStatesHash()
    {
        if ($this->states) {
            return $this->states;
        }

        $this->states = $this->toHash($this->getStates());

        return $this->states;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $types = [];

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'am_shipping_type');
        if ($attribute->usesSource()) {
            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $source */
            $source = $attribute->getSource();
            $types = $source->getAllOptions(false);
        }

        return $types;
    }

    /**
     * @return array
     */
    public function getTypesHash()
    {
        if ($this->types) {
            return $this->types;
        }

        $this->types = $this->toHash($this->getTypes(), false);

        return $this->types;
    }

    /**
     * @param $zip
     * @return array('area' => 'AZ', 'district' => '123')
     */
    public function getDataFromZip($zip)
    {
        $dataZip = ['area' => '', 'district' => ''];

        if (!empty($zip)) {
            $zipSpell = str_split($zip);
            foreach ($zipSpell as $element) {
                if ($element === ' ') {
                    break;
                }
                if (is_numeric($element)) {
                    $dataZip['district'] = $dataZip['district'] . $element;
                } elseif (empty($dataZip['district'])) {
                    $dataZip['area'] = $dataZip['area'] . $element;
                }
            }
        }

        return $dataZip;
    }

    /**
     * @param array $regions
     * @return array
     */
    public function addCountriesToStates($regions)
    {
        $hashCountry = $this->getCountriesHash();
        foreach ($regions as $key => $region) {
            if (isset($region['country_id'])) {
                $regions[$key]['label'] = $hashCountry[$region['country_id']] . "/" . $region['label'];
            }
        }

        return $regions;
    }

    /**
     * @param array $options
     * @param bool $needSort
     * @return array
     */
    protected function toHash($options, $needSort = true)
    {
        $hash = [];
        foreach ($options as $option) {
            $hash[$option['value']] = $option['label'];
        }
        if ($needSort) {
            asort($hash);
        }
        $hashAll['0'] = 'All';
        $hash = $hashAll + $hash;
        $options = $hash;

        return $options;
    }

    /**
     * @param $string
     * @return array|string
     */
    public function escapeHtml($string)
    {
        return $this->escaper->escapeHtml($string, ['b', 'i', 'u', 's']);
    }
}
