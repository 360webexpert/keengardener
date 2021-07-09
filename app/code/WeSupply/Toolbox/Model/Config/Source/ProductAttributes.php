<?php

namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class ProductAttributes implements ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     * attributes types allowed to set WeSupply delivery estimation logic
     */
    protected $frontendTypes = [
        'text',
        'textarea',
        'select',
        'multiselect',
        'price',
        'boolean',
        'weight'
    ];

    /**
     * @var array
     * excluded attributes
     */
    protected $excludeSpecific = [
        'category_ids',
        'msrp_display_actual_price_type',
        'options_container',
        'status',
        'page_layout',
        'custom_layout_update',
        'gift_wrapping_price',
        'price_view',
        'quantity_and_stock_status',
        'special_price',
        'tier_price',
        'url_key',
        'visibility'
    ];

    /**
     * ProductAttributes constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_visible', 1)
            ->create();

        $attributeRepository = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );

        $options = [];
        $attributes = $attributeRepository->getItems();
        foreach ($attributes as $attribute) {
            if (
                !in_array($attribute->getFrontendInput(), $this->frontendTypes) ||
                in_array($attribute->getAttributeCode(), $this->excludeSpecific)
            ) {
                continue;
            }

            $optionKey = strtolower(str_replace(' ', '_', $attribute->getFrontendLabel()));
            if (array_key_exists($optionKey, $options)) {
                $this->incrementOptionKey($optionKey, $options);
            }

            $options[$optionKey] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel() . ' (' . $attribute->getAttributeCode() . ')'
            ];
        }

        ksort($options);

        return $options;
    }

    /**
     * @param $optionKey
     * @param $options
     * @param int $crt
     */
    private function incrementOptionKey(&$optionKey, $options, $crt = 1)
    {
        $optionKey .= $crt;
        if (array_key_exists($optionKey, $options)) {
            $crt++;
            $this->incrementOptionKey($optionKey, $options, $crt);
        }
    }
}
