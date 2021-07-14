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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\Search;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder as SourceFilterGroupBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Builder for FilterGroup Data.
 */
class FilterGroupBuilder extends SourceFilterGroupBuilder
{
    /** @var RequestInterface */
    protected $_request;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FilterGroupBuilder constructor.
     *
     * @param ObjectFactory $objectFactory
     * @param FilterBuilder $filterBuilder
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ObjectFactory $objectFactory,
        FilterBuilder $filterBuilder,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->_request = $request;
        $this->storeManager = $storeManager;

        parent::__construct($objectFactory, $filterBuilder);
    }

    /**
     * @return FilterGroupBuilder
     */
    public function cloneObject()
    {
        $cloneObject = clone $this;
        $cloneObject->setFilterBuilder(clone $this->_filterBuilder);

        return $cloneObject;
    }

    /**
     * @param $filterBuilder
     */
    public function setFilterBuilder($filterBuilder)
    {
        $this->_filterBuilder = $filterBuilder;
    }

    /**
     * @param $attributeCode
     *
     * @return $this
     */
    public function removeFilter($attributeCode)
    {
        if (isset($this->data[FilterGroup::FILTERS]) && is_array($this->data[FilterGroup::FILTERS])) {
            foreach ($this->data[FilterGroup::FILTERS] as $key => $filter) {
                if ($filter->getField() === $attributeCode) {
                    if ($attributeCode === 'category_ids'
                        && ($filter->getValue() === $this->_request->getParam('id')
                            || $filter->getValue() === $this->storeManager->getStore()->getRootCategoryId()
                        )
                    ) {
                        continue;
                    }
                    unset($this->data[FilterGroup::FILTERS][$key]);
                }
            }
        }

        return $this;
    }

    /**
     * @param $categoryIds
     *
     * @return $this
     */
    public function setCategoryFilter($categoryIds)
    {
        if (isset($this->data[FilterGroup::FILTERS]) && is_array($this->data[FilterGroup::FILTERS])) {
            foreach ($this->data[FilterGroup::FILTERS] as $key => $filter) {
                if ($filter->getField() === 'category_ids') {
                    $filter->setValue($categoryIds);
                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        return FilterGroup::class;
    }
}
