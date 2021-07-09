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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Block\Adminhtml\Term\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\SeoCrosslinks\Model\Term\Source\ApplyFor;
use Mageplaza\SeoCrosslinks\Model\Term\Source\Direction;
use Mageplaza\SeoCrosslinks\Model\Term\Source\LinkTarget;
use Mageplaza\SeoCrosslinks\Model\Term\Source\Rel;
use Mageplaza\SeoCrosslinks\Model\Term\Source\TargetType;

/**
 * Class Term
 * @package Mageplaza\SeoCrosslinks\Block\Adminhtml\Term\Edit\Tab
 */
class Term extends Generic implements TabInterface
{
    /**
     * Country options
     *
     * @var Yesno
     */
    protected $_booleanOptions;

    /**
     * Target options
     *
     * @var TargetType
     */
    protected $_targetTypeOptions;

    /**
     * Link Target options
     *
     * @var LinkTarget
     */
    protected $_linkTargetOptions;

    /**
     * Rel Options
     *
     * @var Rel
     */
    protected $_relOptions;

    /**
     * Direction Options
     *
     * @var Direction
     */
    protected $_directionOptions;

    /**
     * System Stores
     *
     * @var Store
     */
    protected $_systemStore;

    /**
     * Apply for Options
     *
     * @type ApplyFor
     */
    protected $_applyForOptions;

    /**
     * Term constructor.
     *
     * @param Yesno $booleanOptions
     * @param TargetType $targetTypeOptions
     * @param LinkTarget $linkTargetOptions
     * @param Rel $relOptions
     * @param Direction $directionOptions
     * @param Store $systemStore
     * @param ApplyFor $applyForOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Yesno $booleanOptions,
        TargetType $targetTypeOptions,
        LinkTarget $linkTargetOptions,
        Rel $relOptions,
        Direction $directionOptions,
        Store $systemStore,
        ApplyFor $applyForOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->_booleanOptions    = $booleanOptions;
        $this->_targetTypeOptions = $targetTypeOptions;
        $this->_linkTargetOptions = $linkTargetOptions;
        $this->_relOptions        = $relOptions;
        $this->_systemStore       = $systemStore;
        $this->_directionOptions  = $directionOptions;
        $this->_applyForOptions   = $applyForOptions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\SeoCrosslinks\Model\Term $term */
        $term = $this->_coreRegistry->registry('mageplaza_seocrosslinks_term');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('term_');
        $form->setFieldNameSuffix('term');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Term Information'),
            'class'  => 'fieldset-wide'
        ]);

        if ($term->getId()) {
            $fieldset->addField('term_id', 'hidden', ['name' => 'term_id']);
        }

        $fieldset->addField('keyword', 'text', [
            'name'     => 'keyword',
            'label'    => __('Keyword'),
            'title'    => __('Keyword'),
            'note'     => __('It will find this keyword and replace by Internal link.'),
            'required' => true
        ]);

        $fieldset->addField('status', 'select', [
            'name'   => 'status',
            'label'  => __('Enable'),
            'title'  => __('Enable'),
            'values' => $this->_booleanOptions->toOptionArray(),
        ]);

        $fieldset->addField('link_title', 'text', [
            'name'     => 'link_title',
            'required' => true,
            'label'    => __('Link Alt/Title'),
            'title'    => __('Link Alt/Title'),
            'note'     => __('<p>Short description for this keyword. This will insert into title attribute. We suggest you add Keywords that you want to rank.</p><b>E.g:</b> Buy iPhone in New York'),
        ]);

        $fieldset->addField('link_target', 'select', [
            'name'   => 'link_target',
            'label'  => __('Link Target'),
            'title'  => __('Link Target'),
            'note'   => __('Target\'s attribute of Internal link. If you want to open new tab,<i> should choose: _blank.</i>'),
            'values' => array_merge($this->_linkTargetOptions->toOptionArray()),
        ]);

        $reference = $fieldset->addField('reference', 'select', [
            'name'   => 'reference',
            'label'  => __('Link to'),
            'title'  => __('Link to'),
            'note'   => '
					- Define the form of address that Internal link refers to.<br>
					- There are three options:<br>
					<p style="margin-left: 20px">
						+ <b>Custom Link:</b>  the URL\'s path will be put together with domain of this website.<br>
						 	<b style="margin-left: 20px">E.g:</b> /iphone.html . This value does not include domain name.<br>
						+ <b>Product Sku:</b> base on SKU link to the product URL.<br>
						 	<b style="margin-left: 20px">E.g:</b> iphone<br>
						+ <b>Category ID:</b> base on ID link to the category URL.<br>
						 	<b style="margin-left: 20px">E.g:</b> 23<br>
					</p>
					',
            'values' => array_merge($this->_targetTypeOptions->toOptionArray()),
        ]);

        $customUrl = $fieldset->addField('ref_static_url', 'text', [
            'name'     => 'ref_static_url',
            'label'    => __('Custom URL'),
            'title'    => __('Custom URL'),
            'required' => true,
        ]);

        $productSku = $fieldset->addField('ref_product_sku', 'text', [
            'name'     => 'ref_product_sku',
            'label'    => __('Product SKU'),
            'title'    => __('Product SKU'),
            'required' => true,
        ]);

        $categoryId = $fieldset->addField('ref_category_id', 'text', [
            'name'     => 'ref_category_id',
            'label'    => __('Category ID'),
            'title'    => __('Category ID'),
            'required' => true,
        ]);

        $dependency = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($reference->getHtmlId(), $reference->getName())
            ->addFieldMap($customUrl->getHtmlId(), $customUrl->getName())
            ->addFieldMap($productSku->getHtmlId(), $productSku->getName())
            ->addFieldMap($categoryId->getHtmlId(), $categoryId->getName())
            ->addFieldMap($customUrl->getHtmlId(), $customUrl->getName())
            ->addFieldDependence($customUrl->getName(), $reference->getName(), TargetType::CUSTOM_URL)
            ->addFieldDependence($productSku->getName(), $reference->getName(), TargetType::PRODUCT_SKU)
            ->addFieldDependence($categoryId->getName(), $reference->getName(), TargetType::CATEGORY);

        $this->setChild('form_after', $dependency);

        $fieldset->addField('stores', 'multiselect', [
            'name'     => 'stores[]',
            'label'    => __('Store View'),
            'title'    => __('Store View'),
            'required' => true,
            'values'   => array_merge($this->_systemStore->getStoreValuesForForm(false, true)),
        ]);

        $fieldset->addField('apply_for', 'multiselect', [
            'name'     => 'apply_for[]',
            'label'    => __('Apply For'),
            'title'    => __('Apply For'),
            'note'     => __('Entities which term will apply for.'),
            'required' => true,
            'values'   => array_merge($this->_applyForOptions->toOptionArray()),
        ]);

        $fieldset->addField('limit', 'text', [
            'name'     => 'limit',
            'label'    => __('Limit Number Of Links Per Page'),
            'title'    => __('Limit Number Of Links Per Page'),
            'note'     => __('Limit the number of replacement keyword per page.'),
            'required' => true
        ]);

        $fieldset->addField('direction', 'select', [
            'name'   => 'direction',
            'label'  => __('Direction'),
            'title'  => __('Direction'),
            'note'   => __('Specific direction which Mageplaza SEO will find and replace the keyword. Suggest use Random or Top.'),
            'values' => array_merge($this->_directionOptions->toOptionArray()),
        ]);

        $fieldset->addField('rel', 'select', [
            'name'   => 'rel',
            'label'  => __('Rel'),
            'title'  => __('Rel'),
            'note'   => __('The rel\'s atrribute of Internal link. Default value: default.'),
            'values' => $this->_relOptions->toOptionArray(),
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name'     => 'sort_order',
            'label'    => __('Priority'),
            'title'    => __('Priority'),
            'required' => true,
            'note'     => __('The priority of this keyword. 0 is highest.'),
        ]);

        $termData = $this->_session->getData('mageplaza_seocrosslinks_term_data', true);
        if ($termData) {
            $term->addData($termData);
        } elseif (!$term->getId()) {
            $term->addData($term->getDefaultValues());
        }
        $form->addValues($term->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Term');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
