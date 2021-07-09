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

namespace Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Design\Robots as MetaRobots;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\SeoRule\Model\Rule;
use Mageplaza\SeoRule\Model\Rule\Source\ApplyTemplate;

/**
 * Class Action
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab
 */
class Action extends Generic implements TabInterface
{
    /**
     * Apply Template options
     *
     * @var ApplyTemplate
     */
    protected $applyTemplateOptions;

    /**
     * @var MetaRobots
     */
    protected $metaRobotsOptions;

    /**
     * Action constructor.
     *
     * @param ApplyTemplate $applyTemplateOptions
     * @param MetaRobots $metaRobotsOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        ApplyTemplate $applyTemplateOptions,
        MetaRobots $metaRobotsOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->applyTemplateOptions = $applyTemplateOptions;
        $this->metaRobotsOptions    = $metaRobotsOptions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Rule $rule */
        $rule = $this->_coreRegistry->registry('mageplaza_seorule_rule');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend'      => __('Action'),
            'class'       => 'fieldset-wide',
            'collapsible' => true,
        ]);
        $fieldset->addField('meta_title', 'textarea', [
            'name'  => 'meta_title',
            'label' => __('Meta Title Template'),
            'title' => __('Meta Title Template'),
            'note'  => __('
                    - Should be 55-60 characters. Use following syntax to add dynamic meta title: {{name}}, {{price}}, {{special_price}}. <a href="https://www.mageplaza.com/magento-2-seo-extension/rule-syntax.html" target="_blank">Learn more</a>.</br>
                    - Auto generate meta data following by syntax:
                    <div style="margin-left: 30px">
                        Attributes is existing in entities type </br>
                        + <b>Attribute:</b> <i>{{attribute}}</i></br>
                        + <b>Random options:</b> [option1|option2|option3|...]</br>
                    </div>
                    - Example:
                    <div style="margin-left: 30px">
                        + <b>{{name}} {{color}} [{{manufacturer}}||{{brand}}] [for||for only] [{{price}}||{{special_price}}] in {{category_name}} category</b></br>
                        => Result: <i>Atlas Fitness Tank White Adidas for special price $49 in Tanks category</i>
                    </div>
                '),
        ]);
        $fieldset->addField('meta_description', 'textarea', [
            'name'  => 'meta_description',
            'label' => __('Meta Description Template'),
            'title' => __('Meta Description Template'),
            'note'  => __('It is best to keep meta descriptions between 150 and 160 characters. It is same with Meta Title syntax. <a href="https://www.mageplaza.com/magento-2-seo-extension/rule-syntax.html" target="_blank">Learn more</a>. '),
        ]);
        $fieldset->addField('meta_keywords', 'textarea', [
            'name'  => 'meta_keywords',
            'label' => __('Meta Keywords Template'),
            'title' => __('Meta Keywords Template'),
            'note'  => __('It is best to keep meta keywords between 3-5 keywords. It is same with Meta Title syntax. <a href="https://www.mageplaza.com/magento-2-seo-extension/rule-syntax.html" target="_blank">Learn more</a>. '),
        ]);
        $fieldset->addField('meta_robots', 'select', [
            'name'   => 'meta_robots',
            'label'  => __('Robots Template'),
            'title'  => __('Robots Template'),
            'values' => array_merge($this->metaRobotsOptions->toOptionArray()),
        ]);
        $fieldset->addField('apply_template', 'select', [
            'required' => true,
            'name'     => 'apply_template',
            'label'    => __('Apply Template'),
            'title'    => __('Apply Template'),
            'note'     => __('<b>Skip if ready defined</b>: if admin manually add meta tag: title, description, so it will not apply this template.'),
            'values'   => array_merge($this->applyTemplateOptions->toOptionArray()),
        ]);

        $form->addValues($rule->getData());
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
        return __('Action');
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
