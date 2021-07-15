<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SeoRule\Controller\Adminhtml\Rule;
use Zend_Serializer_Exception;

/**
 * Class Edit
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Zend_Serializer_Exception
     */
    public function execute()
    {
        $id   = $this->getRequest()->getParam('rule_id');
        $type = $this->getRequest()->getParam('type');
        $this->coreRegistry->register('seorule_type', $type);
        $this->_getSession()->setSeoRuleType($type);
        $rule = $this->seoRuleFactory->create();

        if ($id) {
            $rule->load($id);
            $this->coreRegistry->register('seorule_category', $rule->getCategorys());
            $this->_getSession()->setSeoRulePages($rule->getPages() ? $this->helperData->unserialize($rule->getPages()) : '');
        } else {
            $this->_getSession()->setSeoRulePages('');
        }
        $this->coreRegistry->register('mageplaza_seorule_rule', $rule);

        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend($id ? $rule->getName() : __('New Seo Rule'));
        $this->_view->renderLayout();
    }
}
