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

use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mageplaza\FreeGifts\Model\Source\Apply as ApplyType;

/**
 * Class Conditions
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab
 */
class Conditions extends AbstractTab
{
    /**
     * @return AbstractTab|void
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $rule = $this->getCurrentRule();

        $formName = 'rule_conditions_fieldset';
        $conditionsFieldSetId = $rule->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getNewChildUrl($conditionsFieldSetId, $formName, 'conditions');
        $renderer = $this->_fieldset->setTemplate(self::RENDERER_TEMPLATE)
            ->setNewChildUrl($this->getUrl($newChildUrl))
            ->setFieldSetId($conditionsFieldSetId);

        $conditionsFieldset = $form->addFieldset('conditions_fieldset', [
            'legend' => $this->getFieldsetLegend($rule->getApplyFor()),
        ])->setRenderer($renderer);

        $conditionsFieldset->addField('conditions', 'text', [
            'name' => 'conditions',
            'label' => __('Condition'),
            'title' => __('Condition')
        ])->setRule($rule)->setRenderer($this->_conditions);

        /** @var DataForm $formName */
        // set JS handle for conditions fieldset
        $rule->getConditions()->setJsFormObject($formName);
        $this->setConditionFormName($rule->getConditions(), $formName);

        $form->setValues($rule->getData());
        $this->setForm($form);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * @param $apply
     *
     * @return Phrase
     */
    public function getFieldsetLegend($apply)
    {
        return $apply === ApplyType::ITEM
            ? __('Conditions (don\'t add conditions if rule is applied to all products)')
            : __('Apply the rule only if the following conditions are met (leave blank for all products)');
    }
}
