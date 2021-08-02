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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class UpdateGift
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions
 */
class UpdateGift extends Action
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * UpdateGift constructor.
     *
     * @param Context $context
     * @param HelperRule $helperRule
     * @param Session $catalogSession
     */
    public function __construct(
        Context $context,
        HelperRule $helperRule,
        Session $catalogSession
    ) {
        $this->_helperRule = $helperRule;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $giftId = (int)$this->getRequest()->getParam('gift_id');
        $giftData = [
            'discount' => $params['discount_type'],
            'gift_price' => isset($params['gift_price']) ? (float)$params['gift_price'] : 0,
            'free_ship' => (int)$params['free_shipping'],
        ];

        if ($ruleId = (int)$this->getRequest()->getParam('rule_id')) {
            $rule = $this->_helperRule->getRuleById($ruleId);
            $giftArray = $rule->getGiftArray();
            $giftArray[$giftId] = $giftData;

            $rule->setGifts(HelperData::jsonEncode($giftArray));
            $rule->save();
        } else {
            $giftArray = $this->_catalogSession->getNewGifts();
            $giftArray[$giftId] = $giftData;
            $this->_catalogSession->setNewGifts($giftArray);
        }
    }
}
