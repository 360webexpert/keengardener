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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\Rule\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Attribute
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class Attribute implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Attribute constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[]           = [
            'value' => 'all',
            'label' => 'All'
        ];
        $attributeCollection = $this->getAttributeCollection();
        foreach ($attributeCollection as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => __($attribute->getAttributeCode())
            ];
        }

        return $options;
    }

    /**
     * Get list attribute
     *
     * @param bool $toString
     * @param string $glue
     *
     * @return array|string
     */
    public function getListAttribute($toString = false, $glue = '')
    {
        $attributes = [];
        foreach ($this->getAttributeCollection() as $attribute) {
            if ($toString) {
                $attributes[] = '{{' . $attribute->getAttributeCode() . '}}';
            } else {
                $attributes[] = $attribute->getAttributeCode();
            }
        }
        if ($toString) {
            $attributes = implode($glue, $attributes);
        }

        return $attributes;
    }

    /**
     * Get attribute collection
     * @return mixed
     */
    public function getAttributeCollection()
    {
        return $this->_collectionFactory->create()->addFieldToFilter('is_filterable', ['eq' => 1]);
    }
}
