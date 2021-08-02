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
 * Class Delete
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class Delete extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        if ($ruleId) {
            $rule = $this->_initObject();
            $rule->load($ruleId);

            try {
                $rule->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the rule'));

                return $this->getResultRedirect('mpfreegifts/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->getResultRedirect('mpfreegifts/*/edit', ['rule_id' => $ruleId]);
            }
        }

        $this->messageManager->addErrorMessage(__('This rule no longer exists'));

        return $this->getResultRedirect('mpfreegifts/*/');
    }
}
