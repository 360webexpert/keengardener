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
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AddGift
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions
 */
class AddGift extends Action
{
    /**
     * @var JsHelper
     */
    protected $_jsHelper;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * AddGift constructor.
     *
     * @param Context $context
     * @param JsHelper $jsHelper
     * @param HelperRule $helperRule
     * @param Json $json
     * @param Session $catalogSession
     */
    public function __construct(
        Context $context,
        JsHelper $jsHelper,
        HelperRule $helperRule,
        Json $json,
        Session $catalogSession
    ) {
        $this->_jsHelper = $jsHelper;
        $this->_helperRule = $helperRule;
        $this->_json = $json;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $productIds = $this->getRequest()->getParam('mpfreegifts_product_ids');
        if ($productIds && count($productIds)) {
            $newGifts = [];
            $productIds = array_map('intval', $productIds);
            foreach ($productIds as $productId) {
                $newGifts[$productId] = [
                    'discount' => 'free',
                    'gift_price' => 0,
                    'free_ship' => 1,
                ];
            }

            if ((int)$ruleId = $this->getRequest()->getParam('ruleId')) {
                $currentRule = $this->_helperRule->getRuleById($ruleId);
                $gifts = $currentRule->getGiftArray();
                $currentRule->setGifts(HelperData::jsonEncode($gifts + $newGifts));
                $currentRule->save();
            } else {
                $this->_catalogSession->setNewGifts($newGifts);
            }
        }

        return $this->_json->setData(['data' => null]);
    }
}
