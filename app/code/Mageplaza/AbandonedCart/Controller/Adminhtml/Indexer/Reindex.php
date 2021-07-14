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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Controller\Adminhtml\Indexer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\AbandonedCart\Cron\Indexer;

/**
 * Class Reindex
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Indexer
 */
class Reindex extends Action
{
    /**
     * @var Indexer
     */
    protected $_indexer;

    /**
     * Reindex constructor.
     *
     * @param Action\Context $context
     * @param Indexer $indexer
     */
    public function __construct(
        Action\Context $context,
        Indexer $indexer
    ) {
        $this->_indexer = $indexer;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->_indexer->setIsReindex(1)->execute();
            $this->messageManager->addSuccessMessage(__('Update Success'));
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong. %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('adminhtml/system_config/edit/section/abandonedcart/');
    }
}
