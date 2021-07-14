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

namespace Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class Category
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class Category extends Generic implements TabInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->setTemplate('rule/category/conditions.phtml');

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $form->addFieldset('base_fieldset', [
            'legend'      => __('Condition'),
            'class'       => 'fieldset-wide',
            'collapsible' => true,
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get category ids update
     *
     * @return  array
     */
    public function getCategoryIds()
    {
        $ids = [];
        if ($conditions = $this->registry->registry('seorule_category')) {
            $ids = explode(',', $conditions);
        }

        return $ids;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getCategoryTree()
    {
        $ids   = $this->getCategoryIds();
        $block = $this->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree',
            'seo_rule_widget_chooser_category_ids',
            ['data' => ['js_form_object' => 'seo_rule_form']]
        )->setCategoryIds(
            $ids
        );

        return $block->toHtml();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Condition');
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
