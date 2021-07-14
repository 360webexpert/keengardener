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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\NoRoute;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\SeoDashboard\Controller\Adminhtml\Report;

/**
 * Class Grid
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\NoRoute
 */
class Grid extends Report
{
    /**
     * @type LayoutFactory|null
     */
    protected $_resultLayoutFactory;

    /**
     * Grid constructor.
     *
     * @param Action\Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->_resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Layout
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();

        return $resultLayout;
    }
}
