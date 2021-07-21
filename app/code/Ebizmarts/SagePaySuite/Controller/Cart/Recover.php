<?php

namespace Ebizmarts\SagePaySuite\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Ebizmarts\SagePaySuite\Model\RecoverCart;

class Recover extends Action
{
    /** @var RecoverCart */
    private $recoverCart;

    /**
     * Recover constructor.
     * @param Context $context
     * @param RecoverCart $recoverCart
     */
    public function __construct(
        Context $context,
        RecoverCart $recoverCart
    ) {
        parent::__construct($context);
        $this->recoverCart = $recoverCart;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->recoverCart->setShouldCancelOrder(false)->execute();
        return $this->redirectToCart();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function redirectToCart()
    {
        $redirectUrl = 'checkout/cart';
        return $this->_redirect($redirectUrl);
    }
}
