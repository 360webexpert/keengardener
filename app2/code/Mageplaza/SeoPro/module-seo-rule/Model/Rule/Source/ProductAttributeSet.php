<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model\Rule\Source;

use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductAttributeSet
 * @package Mageplaza\SeoRule\Model\Rule\Source
 */
class ProductAttributeSet implements ArrayInterface
{
    const PRODUCT_ENTITY_TYPE = 4;

    /**
     * @var SetFactory
     */
    protected $attributeSetFactory;

    /**
     * ProductAttributeSet constructor.
     *
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(SetFactory $attributeSetFactory)
    {
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = null;
        $i       = 0;

        $attributeCollection = $this->attributeSetFactory->create()->getCollection();
        foreach ($attributeCollection as $attribute) {
            if ($attribute->getEntityTypeId() == self::PRODUCT_ENTITY_TYPE) {
                $options[$i++] = [
                    'value' => $attribute->getAttributeSetId(),
                    'label' => __($attribute->getAttributeSetName())
                ];
            }
        }

        return $options;
    }
}
