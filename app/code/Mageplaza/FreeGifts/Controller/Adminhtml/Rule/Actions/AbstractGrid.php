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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class AbstractGrid
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions
 */
abstract class AbstractGrid extends Action
{
    /**
     * @var RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Gift constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        PageFactory $resultPageFactory
    ) {
        $this->_resultRawFactory = $resultRawFactory;
        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function getResultRaw()
    {
        return $this->_resultRawFactory->create();
    }

    /**
     * @return ResultPage
     */
    public function getResultPage()
    {
        return $this->_resultPageFactory->create();
    }
}
