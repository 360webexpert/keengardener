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

namespace Mageplaza\FreeGifts\Controller\Gift;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Mageplaza\FreeGifts\Helper\Gift as HelperGift;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AbstractGift
 * @package Mageplaza\FreeGifts\Controller\Gift
 */
abstract class AbstractGift extends Action
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * AbstractGift constructor.
     *
     * @param Context $context
     * @param HelperRule $helperRule
     * @param Cart $cart
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     * @param Json $json
     */
    public function __construct(
        Context $context,
        HelperRule $helperRule,
        Cart $cart,
        Registry $registry,
        CheckoutSession $checkoutSession,
        Json $json
    ) {
        $this->_helperRule = $helperRule;
        $this->_cart = $cart;
        $this->_registry = $registry;
        $this->_json = $json;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * @param $message
     *
     * @return Json
     */
    public function errorMessage($message)
    {
        return $this->_json->setData(['error' => true, 'message' => $message]);
    }

    /**
     * @return array
     */
    public function getRequestParams()
    {
        $params = $this->getRequest()->getParams();
        $data = [
            'gift_id' => (int)$params['gift_id'],
            'rule_id' => (int)$params['rule_id'],
        ];

        if (isset($params['super_attribute'])) {
            $data['super_attribute'] = $params['super_attribute'];
        }

        return $data;
    }

    /**
     * @param array $gifts
     * @param int $giftId
     *
     * @return mixed
     */
    public function removeDeletedGift($gifts, $giftId)
    {
        foreach ($gifts as $index => $deleteGift) {
            if ($giftId === (int)$deleteGift) {
                unset($gifts[$index]);
            }
        }

        return $gifts;
    }

    /**
     * @return HelperGift
     */
    public function getHelperGift()
    {
        return $this->_helperRule->getHelperGift();
    }

    /**
     * @return Json
     * @throws NoSuchEntityException
     */
    public function addGift()
    {
        $ruleId = (int)$this->getRequest()->getParam('rule_id');
        $giftId = (int)$this->getRequest()->getParam('gift_id');
        $gift = $this->getHelperGift()->getProductById($giftId);

        if ($this->getHelperGift()->isGiftAdded($giftId)) {
            return $this->errorMessage(__('This gift is already added.'));
        }
        if ($this->getHelperGift()->isMaxGift($ruleId)) {
            return $this->errorMessage(__('Maximum number of gifts added.'));
        }
        if (!$this->getHelperGift()->isGiftInStock($giftId)) {
            return $this->errorMessage(__('This gift is currently out of stock.'));
        }
        if ($links = $this->getHelperGift()->requireLinks($gift)) {
            $giftParams['links'] = $links;
        }

        $giftParams['product'] = $gift->getId();
        $giftParams[HelperRule::OPTION_RULE_ID] = $ruleId;
        $gift->addCustomOption(HelperRule::QUOTE_RULE_ID, $ruleId);
        $this->getRequest()->setParams($giftParams);

        $deletedGifts = $this->_checkoutSession->getFreeGiftsDeleted();
        if (isset($deletedGifts[$ruleId])) {
            $deletedGifts[$ruleId] = $this->removeDeletedGift($deletedGifts[$ruleId], $giftId);
        }
        $this->_checkoutSession->setFreeGiftsDeleted($deletedGifts);

        try {
            $this->_cart->addProduct($gift, $this->getRequest()->getParams());
            $this->_cart->save();

            return $this->_json->setData(['success' => true]);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }
}
