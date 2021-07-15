<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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

namespace Mageplaza\LayeredNavigationPro\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigationPro\Model\Config\Source\FilterType;

/**
 * Class Layer
 * @package Mageplaza\LayeredNavigationPro\Block\Adminhtml\Product\Attribute\Edit\Tab
 */
class Layer extends Form implements TabInterface
{
    /** @var \Magento\Config\Model\Config\Source\Yesno */
    protected $_yesNo;

    /** @var \Mageplaza\LayeredNavigationPro\Model\Config\Source\FilterType */
    protected $_filterType;

    /** @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory */
    protected $_fieldFactory;

    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $layerHelper;

    /** @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker */
    private $propertyLocker;

    /**
     * Layer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param FilterType $filterType
     * @param PropertyLocker $propertyLocker
     * @param FieldFactory $fieldFactory
     * @param LayerHelper $layerHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        FilterType $filterType,
        PropertyLocker $propertyLocker,
        FieldFactory $fieldFactory,
        LayerHelper $layerHelper,
        array $data = []
    ) {
        $this->_yesNo = $yesNo;
        $this->_filterType = $filterType;
        $this->propertyLocker = $propertyLocker;
        $this->_fieldFactory = $fieldFactory;
        $this->layerHelper = $layerHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('ProductsPage Navigation Properties');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('ProductsPage Navigation Properties');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Attribute $attributeObject */
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');
        $this->layerHelper->getFilterModel()->prepareAttributeData($attributeObject);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $yesnoSource = $this->_yesNo->toOptionArray();

        $fieldset = $form->addFieldset('layer_fieldset', ['legend' => __('ProductsPage Navigation Properties')]);

        $fieldset->addField('is_filterable', 'select', [
            'name'   => 'is_filterable',
            'label'  => __("Use in ProductsPage Navigation"),
            'title'  => __('Can be used only with catalog input type Dropdown, Multiple Select, Price'),
            'note'   => __('Can be used only with catalog input type Dropdown, Multiple Select, Price.'),
            'values' => [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Filterable (with results)')],
                ['value' => '2', 'label' => __('Filterable (no results)')],
            ],
        ]);

        $fieldset->addField('is_filterable_in_search', 'select', [
            'name'   => 'is_filterable_in_search',
            'label'  => __("Use in Search Results ProductsPage Navigation"),
            'title'  => __('Can be used only with catalog input type Dropdown, Multiple Select, Price'),
            'note'   => __('Can be used only with catalog input type Dropdown, Multiple Select, Price.'),
            'values' => $yesnoSource
        ]);

        $fieldset->addField('position', 'text', [
            'name'  => 'position',
            'label' => __('Position'),
            'title' => __('Position in ProductsPage Navigation'),
            'note'  => __('Position of attribute in layered navigation block.'),
            'class' => 'validate-digits'
        ]);

        array_unshift($yesnoSource, ['value' => 2, 'label' => __('Use Config Settings')]);

        $fieldset->addField(LayerHelper::FIELD_ALLOW_MULTIPLE, 'select', [
            'name'   => LayerHelper::FIELD_ALLOW_MULTIPLE,
            'label'  => __('Allow Multiple Filter'),
            'title'  => __('Allow Multiple Filter'),
            'class'  => 'layer_attribute_field',
            'values' => $yesnoSource,
        ]);

        $fieldset->addField(LayerHelper::FIELD_FILTER_TYPE, 'select', [
            'name'   => LayerHelper::FIELD_FILTER_TYPE,
            'label'  => __('Display Style'),
            'title'  => __('Display Style'),
            'class'  => 'layer_attribute_field',
            'values' => $this->_filterType->toOptionArray()
        ]);

        $fieldset->addField(LayerHelper::FIELD_SEARCH_ENABLE, 'select', [
            'name'   => LayerHelper::FIELD_SEARCH_ENABLE,
            'label'  => __('Enable Option Search'),
            'title'  => __('Enable Option Search'),
            'class'  => 'layer_attribute_field',
            'values' => $yesnoSource,
        ]);

        $fieldset->addField(LayerHelper::FIELD_IS_EXPAND, 'select', [
            'name'   => LayerHelper::FIELD_IS_EXPAND,
            'label'  => __('Expand by default'),
            'title'  => __('Expand by default'),
            'class'  => 'layer_attribute_field',
            'values' => $yesnoSource,
        ]);

        $refField = $this->_fieldFactory->create(['fieldData'   => ['value' => '1,2', 'separator' => ','],
                                                  'fieldPrefix' => ''
        ]);
        $dependencies = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap("is_filterable", 'is_filterable')
            ->addFieldMap(LayerHelper::FIELD_ALLOW_MULTIPLE, LayerHelper::FIELD_ALLOW_MULTIPLE)
            ->addFieldMap(LayerHelper::FIELD_FILTER_TYPE, LayerHelper::FIELD_FILTER_TYPE)
            ->addFieldMap(LayerHelper::FIELD_SEARCH_ENABLE, LayerHelper::FIELD_SEARCH_ENABLE)
            ->addFieldMap(LayerHelper::FIELD_IS_EXPAND, LayerHelper::FIELD_IS_EXPAND)
            ->addFieldDependence(LayerHelper::FIELD_ALLOW_MULTIPLE, 'is_filterable', $refField)
            ->addFieldDependence(LayerHelper::FIELD_FILTER_TYPE, 'is_filterable', $refField)
            ->addFieldDependence(LayerHelper::FIELD_SEARCH_ENABLE, 'is_filterable', $refField)
            ->addFieldDependence(LayerHelper::FIELD_IS_EXPAND, 'is_filterable', $refField);

        $this->_eventManager->dispatch('product_attribute_form_build_layer_tab', [
            'form'         => $form,
            'attribute'    => $attributeObject,
            'dependencies' => $dependencies
        ]);

        // define field dependencies
        $this->setChild('form_after', $dependencies);

        $this->setForm($form);
        $form->setValues($attributeObject->getData());
        $this->propertyLocker->lock($form);

        return parent::_prepareForm();
    }
}
