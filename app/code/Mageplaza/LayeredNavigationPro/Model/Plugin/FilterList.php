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

use Closure;
use Magento\Catalog\Model\Layer;
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\LayeredNavigation\Model\Layer\Filter\Category;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class FilterList
 * @package Mageplaza\LayeredNavigationPro\Model\Plugin
 */
class FilterList
{
    const RATING_FILTER = 'layer_rating';
    const STATE_FILTER  = 'layer_state';

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Data */
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
     * @param ObjectManagerInterface $objectManager
     * @param Data $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Data $helper
    ) {
        $this->objectManager = $objectManager;
        $this->helper        = $helper;
    }

    /**
     * @param Layer\FilterList $subject
     * @param Closure $proceed
     * @param Layer $layer
     *
     * @return $this|array
     */
    public function aroundGetFilters(
        Layer\FilterList $subject,
        Closure $proceed,
        Layer $layer
    ) {
        $filters = $proceed($layer);

        if (!$this->helper->isEnabled()) {
            return $filters;
        }

        $stateConfig  = $this->helper->getFilterConfig('state');
        $ratingConfig = $this->helper->getFilterConfig('rating');
        if (!$this->customFilter) {
            $customFilter = [];

            if ($stateConfig['new_enable'] || $stateConfig['onsales_enable'] || $stateConfig['stock_enable']) {
                $customFilter['state'] = $this->objectManager->create(
                    $this->filterTypes[self::STATE_FILTER],
                    ['data' => ['position' => $stateConfig['position']], 'layer' => $layer]
                );
            }

            if (isset($ratingConfig['rating_enable']) && $ratingConfig['rating_enable']) {
                $customFilter['rating'] = $this->objectManager->create(
                    $this->filterTypes[self::RATING_FILTER],
                    ['data' => ['position' => $ratingConfig['position']], 'layer' => $layer]
                );
            }

            $this->customFilter = $customFilter;
        }

        if (!empty($this->customFilter)) {
            $filters = $this->sortFilterByPosition(array_merge($filters, $this->customFilter));
        }

        return $filters;
    }

    /**
     * @param $filters
     *
     * @return array
     */
    protected function sortFilterByPosition($filters)
    {
        if (is_array($filters)) {
            usort($filters, function ($filterA, $filterB) {
                if ($this->getPosition($filterA) === $this->getPosition($filterB)) {
                    return $filterA->getName() < $filterB->getName() ? -1 : 1;
                }

                return ($this->getPosition($filterA) < $this->getPosition($filterB)) ? -1 : 1;
            });
        }

        return $filters;
    }

    /**
     * @param $object
     *
     * @return int
     */
    private function getPosition($object)
    {
        if ($object instanceof Category) {
            return -2;
        }

        $attribute = $object->hasAttributeModel() ? $object->getAttributeModel() : null;
        $position  = $object->hasPosition() ? $object->getPosition() : ($attribute ? $attribute->getPosition() : 0);

        return $position;
    }
}
