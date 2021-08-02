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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Actions
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends AbstractTab
{
    /**
     * @return AbstractTab
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $rule = $this->getCurrentRule();
        $ruleId = $this->getRuleId();
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $actionsFieldset = $form->addFieldset('mpfreegifts_actions_fieldset', [
            'legend' => __('Actions'),
            'class' => 'fieldset-wide'
        ]);
        $actionsFieldset->addField('type', 'select', [
            'name' => 'type',
            'label' => __('Type'),
            'title' => __('Type'),
            'values' => $this->_type->toOptionArray(),
            'after_element_html' => $this->getTypeAfterHtml(),
        ]);
        $actionsFieldset->addField('apply_for', 'hidden', [
            'name' => 'apply_for',
            'label' => __('Apply for'),
            'title' => __('Apply for'),
            'value' => $this->getRequest()->getParam('apply'),
        ]);
        $actionsFieldset->addField('number_gift_allowed', 'text', [
            'name' => 'number_gift_allowed',
            'class' => 'validate-number',
            'label' => __('Number of gifts allowed'),
            'title' => __('Number of gifts allowed'),
            'note' => __('If empty or zero, no limitation'),
        ]);
        $actionsFieldset->addField('allow_notice', 'select', [
            'name' => 'allow_notice',
            'label' => __('Show notice for gift'),
            'title' => __('Show notice for gift'),
            'values' => $this->_yesno->toOptionArray(),
            'after_element_html' => $this->getUseConfigHtml('rule_use_config_allow_notice', 'use_config_allow_notice'),
            'note' => __('If yes, a notice will be shown under gift item'),

        ]);
        $actionsFieldset->addField('notice', 'text', [
            'name' => 'notice',
            'label' => __('Notice Content'),
            'title' => __('Notice Content'),
            'value' => $ruleId ? '' : __('You deserve it!'),
            'after_element_html' => $this->getUseConfigHtml('rule_use_config_notice', 'use_config_notice'),
        ]);
        $actionsFieldset->addField('discard_subsequent_rules', 'select', [
            'name' => 'discard_subsequent_rules',
            'label' => __('Discard Subsequent Rules'),
            'title' => __('Discard Subsequent Rules'),
            'values' => $this->_yesno->toOptionArray(),
            'value' => '1'
        ]);

        $listGiftFieldset = $form->addFieldset('mpfreegifts_actions_gift_list_fieldset', [
            'legend' => __('Gift List'),
        ]);

        $listGiftFieldset->addField('gift_list', 'text', [
            'name' => 'gift_list',
        ])->setRenderer($this->_giftListing);

        $form->addValues($rule->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getTypeAfterHtml()
    {
        return '<div class="note-type-field" style="width: 100%; font-size: 12px;"><p><b>'
            . __('Automatic')
            . '</b>: '
            . __('Gifts will be added/removed automatically when items are added/removed')
            . '</p><p><b>'
            . __('Manual')
            . '</b>: '
            . __('A popup will be displayed for customers to select gifts')
            . '</p></div>';
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * @param string $inputId
     * @param string $inputName
     *
     * @return string
     */
    public function getUseConfigHtml($inputId, $inputName)
    {
        return '<div class="mp-freegifts-use-config-settings">
                    <input name="rule[' . $inputName . ']" type="checkbox" id="' . $inputId . '"
                    onchange="this.value = this.checked ? 1 : 0"
                    class="mp-freegifts-use-config"/>
                    <label for="' . $inputId . '" class="label">
                        <span>' . __('Use Config Settings') . '</span>
                    </label>
                </div>';
    }
}
