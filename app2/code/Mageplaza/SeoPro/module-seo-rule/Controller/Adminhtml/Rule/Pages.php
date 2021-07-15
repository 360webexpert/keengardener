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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class Pages
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Pages extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param LayoutFactory $resultLayoutFactory
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);

        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return Layout
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('page')
            ->setPage($this->getRequest()->getPost('page', null));

        return $resultLayout;
    }
}
