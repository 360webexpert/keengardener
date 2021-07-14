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

namespace Mageplaza\LayeredNavigation\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\CategoriesLevel;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\ExpandSubcategories;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\RenderCategoryTree;

/**
 * Class Layer
 * @package Mageplaza\LayeredNavigation\Block\Adminhtml\Product\Attribute\Edit\Tab
 */
class Layer extends Form implements TabInterface
{
    const FIELD_FRONTEND_INPUT  = 'frontend_input';
    const YES_NO_NEGATIVE_VALUE = '0';
    const YES_NO_POSITIVE_VALUE = '1';

    /**
     * @var Attribute
     */
    protected $attributeObject;
    /**
     * @var LayerHelper
     */
    protected $helperData;
    /**
     * @var RenderCategoryTree
     */
    protected $renderCategoryTree;
    /**
     * @var CategoriesLevel
     */
    protected $categoriesLevel;
    /**
     * @var ExpandSubcategories
     */
    protected $expandSubcategories;
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * Layer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param LayerHelper $helperData
     * @param RenderCategoryTree $renderCategoryTree
     * @param CategoriesLevel $categoriesLevel
     * @param ExpandSubcategories $expandSubcategories
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        LayerHelper $helperData,
        RenderCategoryTree $renderCategoryTree,
        CategoriesLevel $categoriesLevel,
        ExpandSubcategories $expandSubcategories,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->attributeObject     = $registry->registry('entity_attribute');
        $this->helperData          = $helperData;
        $this->renderCategoryTree  = $renderCategoryTree;
        $this->categoriesLevel     = $categoriesLevel;
        $this->expandSubcategories = $expandSubcategories;
        $this->fieldFactory        = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Display Properties');
    }

    /**
     * @return Phrase
     */
    public function getTabTitle()
    {
        if ($this->attributeObject->getAttributeCode() !== 'category_ids') {
            return null;
        }

        return __('Display Properties');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return !($this->attributeObject->getAttributeCode() !== 'category_ids');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return ($this->attributeObject->getAttributeCode() !== 'category_ids');
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        if ($this->attributeObject->getAttributeCode() !== 'category_ids') {
            return parent::_prepareForm();
        }

        $this->prepareAttributeData($this->attributeObject);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'      => 'edit_form',
                    'action'  => $this->getData('action'),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $fieldset                = $form->addFieldset(
            'mplayer_fieldset_categories_tree',
            ['legend' => __('Render Categories Tree'), 'collapsable' => $this->getRequest()->has('popup')]
        );
        $renderCategoryTreeField = $fieldset->addField(
            'render_category_tree',
            'select',
            [
                'name'   => 'render_category_tree',
                'label'  => __('Render Category Tree'),
                'title'  => __('Render Category Tree'),
                'values' => $this->renderCategoryTree->toOptionArray(),
                'note'   => __('Please, make sure all parent categories have set Anchor to render full tree')
            ]
        );

        $categoryTreeDepthField = $fieldset->addField(
            'category_tree_depth',
            'text',
            [
                'name'  => 'category_tree_depth',
                'label' => __('Category Tree Depth'),
                'title' => __('Category Tree Depth'),
                'class' => 'validate-digits validate-greater-than-zero',
                'note'  => __('Specify the max level number for category tree. Keep 1 to hide the subcategories'),
            ]
        );

        $renderCategoriesLevelField = $fieldset->addField(
            'categories_level',
            'select',
            [
                'name'   => 'categories_level',
                'label'  => __('Categories Level'),
                'title'  => __('Categories Level'),
                'values' => $this->categoriesLevel->toOptionArray(),
            ]
        );

        $subcategoriesExpandField = $fieldset->addField(
            'expand_subcategories',
            'select',
            [
                'name'   => 'expand_subcategories',
                'label'  => __('Expand Subcategories'),
                'title'  => __('Expand Subcategories'),
                'values' => $this->expandSubcategories->toOptionArray()
            ]
        );

        $refField     = $this->fieldFactory->create([
            'fieldData'   => ['value' => '1,2', 'separator' => ','],
            'fieldPrefix' => ''
        ]);
        $dependencies = $this->getLayout()->createBlock(Dependence::class);
        $dependencies->addFieldMap($renderCategoryTreeField->getHtmlId(), $renderCategoryTreeField->getName())
            ->addFieldMap($categoryTreeDepthField->getHtmlId(), $categoryTreeDepthField->getName())
            ->addFieldMap($renderCategoriesLevelField->getHtmlId(), $renderCategoriesLevelField->getName())
            ->addFieldMap($subcategoriesExpandField->getHtmlId(), $subcategoriesExpandField->getName());
        $dependencies->addFieldDependence(
            $categoryTreeDepthField->getName(),
            $renderCategoryTreeField->getName(),
            RenderCategoryTree::CUSTOM
        )
            ->addFieldDependence(
                $renderCategoriesLevelField->getName(),
                $renderCategoryTreeField->getName(),
                RenderCategoryTree::CUSTOM
            )
            ->addFieldDependence($subcategoriesExpandField->getName(), $renderCategoryTreeField->getName(), $refField);

        $this->_eventManager->dispatch('product_attribute_form_build_layer_category_tab', [
            'form'         => $form,
            'attribute'    => $this->attributeObject,
            'dependencies' => $dependencies
        ]);

        // define field dependencies
        $this->setChild('form_after', $dependencies);

        $this->setForm($form);
        $form->setValues($this->attributeObject->getData());

        return parent::_prepareForm();
    }

    /**
     * @param $attribute
     *
     * @return mixed
     */
    public function prepareAttributeData($attribute)
    {
        if ($data = $attribute->getAdditionalData()) {
            $additionalData = $this->helperData->unserialize($data);
            if (is_array($additionalData)) {
                $attribute->addData($additionalData);
            }
        }

        if ($attribute->getData(LayerHelper::FIELD_RENDER_CATEGORY_TREE) === null) {
            $attribute->setData(LayerHelper::FIELD_RENDER_CATEGORY_TREE, '0');
        }
        if ($attribute->getData(LayerHelper::CATEGORY_TREE_DEPTH) === null) {
            $attribute->setData(LayerHelper::CATEGORY_TREE_DEPTH, '2');
        }
        if ($attribute->getData(LayerHelper::CATEGORIES_LEVEL) === null) {
            $attribute->setData(LayerHelper::CATEGORIES_LEVEL, '1');
        }
        if ($attribute->getData(LayerHelper::EXPAND_SUBCATEGORIES) === null) {
            $attribute->setData(LayerHelper::EXPAND_SUBCATEGORIES, 'click');
        }

        return $attribute;
    }
}
