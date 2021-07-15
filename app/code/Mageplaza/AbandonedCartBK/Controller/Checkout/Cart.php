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

namespace Mageplaza\AbandonedCart\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AbandonedCart\Model\Api\AbandonedCartRepository;

/**
 * Class Cart
 * @package Mageplaza\AbandonedCart\Controller\Checkout
 */
class Cart extends Action
{
    /**
     * @var AbandonedCartRepository
     */
    protected $abandonedCartRepository;

    /**
     * Cart constructor.
     *
     * @param AbandonedCartRepository $abandonedCartRepository
     * @param Context $context
     */
    public function __construct(AbandonedCartRepository $abandonedCartRepository, Context $context)
    {
        $this->abandonedCartRepository = $abandonedCartRepository;

        parent::__construct($context);
    }

    /**
     * Recovery cart by cart link
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $token   = $this->getRequest()->getParam('token');
        $quoteId = (int) $this->getRequest()->getParam('id');

        try {
            if ($this->abandonedCartRepository->recover($token, $quoteId, true)) {
                $this->messageManager->addSuccessMessage(__('The recovery succeeded.'));
            } else {
                $this->messageManager->addNoticeMessage($this->abandonedCartRepository->getNoticeMessage());
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $this->goBack();
    }

    /**
     * @return ResponseInterface
     */
    protected function goBack()
    {
        return $this->_redirect('checkout/cart');
    }
}
