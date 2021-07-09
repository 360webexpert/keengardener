<?php

namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class ProductAttributesSelect extends ProductAttributes
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->addAdditionalOption() + parent::toOptionArray();
    }

    /**
     * @return array
     */
    private function addAdditionalOption()
    {
        return [
            'none' => [
                'value' => '',
                'label' => __('--Please Select--')
            ]
        ];
    }
}
