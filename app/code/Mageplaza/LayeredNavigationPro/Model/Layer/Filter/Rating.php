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

namespace Mageplaza\LayeredNavigationPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Class Rating
 * @package Mageplaza\LayeredNavigationPro\Model\Layer\Filter
 */
class Rating extends AbstractFilter
{
    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $_moduleHelper;

    /**
     * Rating constructor.
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Mageplaza\LayeredNavigationPro\Helper\Data $moduleHelper,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->_moduleHelper = $moduleHelper;
        $this->_requestVar = \Mageplaza\LayeredNavigationPro\Helper\Data::FILTER_TYPE_RATING;
        $this->setData('filter_type', \Mageplaza\LayeredNavigationPro\Helper\Data::FILTER_TYPE_RATING);
        $this->setData('multiple_mode', false);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $productCollection = $this->getLayer()->getProductCollection();
        $productCollection->getSelect()
            ->joinLeft(
                ['rt' => $productCollection->getTable('review_entity_summary')],
                "e.entity_id = rt.entity_pk_value AND rt.store_id = " . $this->_storeManager->getStore()->getId(),
                ['rating_summary']
            );

        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue = explode(',', $attributeValue);

        $rating = min($attributeValue);
        $productCollection->getSelect()->where('rt.rating_summary >= ?', $rating * 20);

        $this->getLayer()->getState()->addFilter($this->_createItem($this->getOptionText($rating), $rating));

        $this->setItems([]); // set items to disable show filtering

        return $this;
    }

    /**
     * @param int $optionId
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getOptionText($optionId)
    {
        if ($optionId == 1) {
            return __('%1 star & up', $optionId);
        }

        return __('%1 stars & up', $optionId);
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return $this->_moduleHelper->getFilterConfig('rating/label') ?: __('Rating');
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $ratingStep = [80, 60, 40, 20];

        /** @var \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        foreach ($ratingStep as $step) {
            $productCollectionClone = clone $productCollection;
            $productCollectionClone->getSelect()->where('rt.rating_summary >= ' . $step);
            $this->itemDataBuilder->addItemData(
                $step / 20 . ' Star',
                $step / 20,
                $productCollectionClone->resetTotalRecords()->getSize()
            );
        }

        return $this->itemDataBuilder->build();
    }
}
