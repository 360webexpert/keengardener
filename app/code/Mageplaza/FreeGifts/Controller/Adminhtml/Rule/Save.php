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

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

/**
 * Class Save
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRuleData();
        $back = $this->getRequest()->getParam('back');
        $rule = $this->_initObject();
        $giftListEmpty = (int)$rule->getId() === 0 && empty($this->_catalogSession->getNewGifts());
        $rule->addData($data);
        $rule->loadPost($data);

        try {
            $rule->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($giftListEmpty || empty($rule->getGiftArray())) {
            $this->messageManager->addErrorMessage(__('Rule with empty gift list will not take effect.'));

            return $this->getResultRedirect('*/*/edit', [
                'rule_id' => $rule->getId(),
                'active_tab' => 'mpfreegifts_actions_tab',
            ]);
        }
        $this->messageManager->addSuccessMessage(__('You saved the rule.'));

        return $back
            ? $this->getResultRedirect('*/*/edit', ['rule_id' => $rule->getId()])
            : $this->getResultRedirect('*/*/');
    }

    /**
     * @return mixed
     */
    public function getRuleData()
    {
        $data = $this->getRequest()->getParam('rule');

        if (!isset($data['use_config_notice'])) {
            $data['use_config_notice'] = '0';
        }
        if (!isset($data['use_config_allow_notice'])) {
            $data['use_config_allow_notice'] = '0';
        }

        return $data;
    }
}
