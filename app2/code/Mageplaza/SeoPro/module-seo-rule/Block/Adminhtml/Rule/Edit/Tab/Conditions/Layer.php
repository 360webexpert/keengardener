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
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\SeoRule\Model\Rule;
use Mageplaza\SeoRule\Model\Rule\Source\Attribute;

/**
 * Class Layer
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class Layer extends Generic
{
    /**
     * @var Attribute
     */
    protected $layer;

    /**
     * Layer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Attribute $layer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Attribute $layer,
        array $data = []
    ) {
        $this->layer = $layer;

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
            'legend'      => __('Rule conditions'),
            'class'       => 'fieldset-wide',
            'collapsible' => true,
        ]);

        $fieldset->addField('attribute', 'select', [
            'name'   => 'attribute',
            'label'  => __('Attribute is'),
            'title'  => __('Attribute'),
            'values' => $this->layer->toOptionArray(),
        ]);

        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
