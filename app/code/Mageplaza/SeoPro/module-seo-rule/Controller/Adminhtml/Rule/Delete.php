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

namespace Mageplaza\SeoRule\Controller\Adminhtml\Rule;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SeoRule\Controller\Adminhtml\Rule;

/**
 * Class Delete
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Delete extends Rule
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id             = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Mageplaza\SeoRule\Model\Rule $rule */
                $rule = $this->seoRuleFactory->create();
                $rule->load($id);
                $name = $rule->getName();
                $rule->delete();
                $this->messageManager->addSuccess(__('The Rule has been deleted.'));

                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_seorule_rule_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('seo/*/');

                return $resultRedirect;
            } catch (Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_seorule_rule_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                $this->messageManager->addError($e->getMessage());

                $resultRedirect->setPath('seo/*/edit', ['rule_id' => $id]);

                return $resultRedirect;
            }
        }

        $this->messageManager->addError(__('Rule to delete was not found.'));

        $resultRedirect->setPath('seo/*/');

        return $resultRedirect;
    }
}
