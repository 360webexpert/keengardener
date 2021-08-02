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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab;

use IntlDateFormatter;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element as StoreSwitcherElement;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Element\StateText;

/**
 * Class General
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab
 */
class General extends AbstractTab
{

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $rule = $this->getCurrentRule();
        $rule_id = $this->getRuleId();
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $generalFieldset = $form->addFieldset('mpfreegifts_general_fieldset', [
            'legend' => __('General'),
            'class' => 'fieldset-wide'
        ]);

        $generalFieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
        ]);

        $generalFieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => $this->_status->toOptionArray()
        ]);

        $generalFieldset->addField('description', 'textarea', [
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description')
        ]);

        if ($rule_id) {
            $generalFieldset->addType('stateText', StateText::class);
            $generalFieldset->addField('state', 'stateText', [
                'name' => '',
                'label' => __('State'),
                'text' => $this->_helperRule->getStateText($rule_id),
            ]);
        }

        /** @var RendererInterface $rendererBlock */
        $rendererBlock = $this->getLayout()->createBlock(StoreSwitcherElement::class);
        $generalFieldset->addField('website_id', 'multiselect', [
            'name' => 'website_id',
            'title' => __('Website'),
            'label' => __('Website'),
            'required' => true,
            'values' => $this->_websites->toOptionArray()
        ])->setRenderer($rendererBlock);

        if (!$rule->hasData('website_id')) {
            $rule->setWebsiteId(1);
        }

        $generalFieldset->addField('customer_group_ids', 'multiselect', [
            'name' => 'customer_group_ids',
            'label' => __('Customer Groups'),
            'title' => __('Customer Groups'),
            'required' => true,
            'values' => $this->_customerGroup->toOptionArray(),
        ]);

        $generalFieldset->addField('from_date', 'date', [
            'name' => 'from_date',
            'label' => __('Active From'),
            'title' => __('Active From'),
            'date_format' => $this->_localeDate->getDateFormat(IntlDateFormatter::MEDIUM),
            'class' => 'validate-date validate-date-range date-range-task_data-from',
            'timezone' => false,
        ]);

        $generalFieldset->addField('to_date', 'date', [
            'name' => 'to_date',
            'label' => __('Active To'),
            'title' => __('Active To'),
            'date_format' => $this->_localeDate->getDateFormat(IntlDateFormatter::MEDIUM),
            'class' => 'validate-date validate-date-range date-range-task_data-to',
            'timezone' => false,
        ]);

        $generalFieldset->addField('priority', 'text', [
            'name' => 'priority',
            'label' => __('Priority'),
            'title' => __('Priority'),
            'class' => 'validate-number validate-zero-or-greater',
            'value' => '0',
            'note' => __('Default is 0, 0 is the highest priority.'),
        ]);

        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('General');
    }
}
