<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Model\Plugin;

/**
 * Class FilterList
 * @package Mageplaza\LayeredNavigationPro\Model\Plugin
 */
class FilterList
{
    const RATING_FILTER = 'layer_rating';
    const STATE_FILTER  = 'layer_state';

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $helper;

    /** @var  array Custom filter */
    protected $customFilter;

    /** @var array Filter Type */
    protected $filterTypes = [
        self::RATING_FILTER => 'Mageplaza\LayeredNavigationPro\Model\Layer\Filter\Rating',
        self::STATE_FILTER  => 'Mageplaza\LayeredNavigationPro\Model\Layer\Filter\State'
    ];

    /**
     * FilterList constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Mageplaza\LayeredNavigationPro\Helper\Data $helper
    ) {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Layer $layer
     *
     * @return $this|array
     */
    public function aroundGetFilters(
        \Magento\Catalog\Model\Layer\FilterList $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $filter = $proceed($layer);

        if (!$this->helper->isEnabled()) {
            return $filter;
        }

        if (!$this->customFilter) {
            $customFilter = [];

            $stateConfig = $this->helper->getFilterConfig('state');
            if ($stateConfig['new_enable'] || $stateConfig['onsales_enable'] || $stateConfig['stock_enable']) {
                $customFilter[] = $this->objectManager->create(
                    $this->filterTypes[self::STATE_FILTER],
                    ['data' => ['position' => $stateConfig['position']], 'layer' => $layer]
                );
            }

            $ratingConfig = $this->helper->getFilterConfig('rating');
            if (isset($ratingConfig['rating_enable']) && $ratingConfig['rating_enable']) {
                $customFilter[] = $this->objectManager->create(
                    $this->filterTypes[self::RATING_FILTER],
                    ['data' => ['position' => $ratingConfig['position']], 'layer' => $layer]
                );
            }

            $this->customFilter = $customFilter;
        }

        if (sizeof($this->customFilter)) {
            $filter = array_merge($filter, $this->customFilter);
        }

        return $filter;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    protected function sortFilter($a, $b)
    {
        $aPosition = $this->getPosition($a);
        $bPosition = $this->getPosition($b);

        return ($aPosition >= $bPosition) ? 1 : -1;
    }

    /**
     * @param $object
     *
     * @return int
     */
    private function getPosition($object)
    {
        $attribute = $object->hasAttributeModel() ? $object->getAttributeModel() : null;
        $position = $object->hasPosition() ? $object->getPosition() : ($attribute ? $attribute->getPosition() : 0);

        return $position;
    }
}
