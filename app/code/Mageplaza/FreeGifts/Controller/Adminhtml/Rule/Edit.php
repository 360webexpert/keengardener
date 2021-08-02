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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Phrase;
use Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

/**
 * Class Edit
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = $this->_initObject();
        if ($ruleId && !$rule->getId()) {
            $this->messageManager->addError(__('The selected rule no longer exists.'));

            return $this->getResultRedirect('mpfreegifts/*/');
        }

        $title = $rule->getId() ? $rule->getName() : $this->getCreateRuleTitle();
        if ($title === null) {
            return $this->getResultRedirect('mpfreegifts/no-route');
        }

        $rule->getConditions()->setFormName('rule_conditions_fieldset');
        $rule->getConditions()->setJsFormObject(
            $rule->getConditionsFieldSetId($rule->getConditions()->getFormName())
        );
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Mageplaza_FreeGifts::rule');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

    /**
     * @return bool|Phrase
     */
    public function getCreateRuleTitle()
    {
        $type = $this->_applyType->getOptionHash();
        $apply = $this->getRequest()->getParam('apply');

        return isset($type[$apply]) ? __('Create New %1', $type[$apply]) : null;
    }
}
