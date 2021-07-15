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

namespace Mageplaza\LayeredNavigation\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Mageplaza\LayeredNavigation\Helper\Data;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;

/**
 * Class Attributes
 * @package Mageplaza\LayeredNavigation\Model\Category\Attribute\Source
 */
class Attributes extends AbstractSource
{
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var FilterableAttributeList
     */
    protected $filterableAttributeList;

    /**
     * Atrributes constructor.
     *
     * @param Data $helperData
     * @param FilterableAttributeList $filterableAttributeList
     */
    public function __construct(
        Data $helperData,
        FilterableAttributeList $filterableAttributeList
    ) {
        $this->helperData = $helperData;
        $this->filterableAttributeList = $filterableAttributeList;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];
            foreach ($this->filterableAttributeList->getList()->getItems() as $attribute) {
                $this->_options[] = [
                    'label' => __($attribute->getFrontendLabel()),
                    'value' => $attribute->getData('attribute_code')
                ];
            }
        }

        return $this->_options;
    }
}
