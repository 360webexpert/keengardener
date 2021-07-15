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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SeoRule\Controller\Adminhtml\Rule;

/**
 * Class Index
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Index extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            $this->messageManager->addWarning(__(
                "Mageplaza SEO extension is being disabled, so rules will be not applied. Please enable it  <a href=\"%1\">here</a>",
                $this->getUrl('adminhtml/system_config/edit/section/seo')
            ));
        }
        $this->_initAction()->_addBreadcrumb(__('SeoRule'), __('Manage Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('SEO Rules'));
        $this->_view->renderLayout();
    }
}
