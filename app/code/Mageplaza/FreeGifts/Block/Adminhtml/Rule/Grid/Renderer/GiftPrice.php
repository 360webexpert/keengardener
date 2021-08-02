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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Price;
use Magento\Catalog\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Registry;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Gift\Listing as GiftListing;
use Mageplaza\FreeGifts\Model\Rule;
use Zend_Currency_Exception;

/**
 * Class GiftPrice
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer
 */
class GiftPrice extends Price
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * GiftPrice constructor.
     *
     * @param Context $context
     * @param CurrencyInterface $localeCurrency
     * @param Registry $registry
     * @param Session $catalogSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrencyInterface $localeCurrency,
        Registry $registry,
        Session $catalogSession,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context, $localeCurrency, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return float|int|mixed|string
     * @throws Zend_Currency_Exception
     */
    public function render(DataObject $row)
    {
        /** @var Rule $rule */
        $rule = $this->_registry->registry('current_rule');
        $gifts = ($rule && (int)$rule->getId()) ? $rule->getGiftArray() : $this->_catalogSession->getNewGifts();
        $giftId = (int)$row->getId();

        isset($gifts[$giftId])
            ? $row->setData('gift_price', $gifts[$giftId]['gift_price'])
            : $row->setData('gift_price', 0);

        if ($row->getData('discount_type') !== GiftListing::TYPE_PERCENT) {
            $data = $this->_getValue($row);
            $currencyCode = $this->_getCurrencyCode($row);
            if (!$currencyCode) {
                return $data;
            }
            $data = (float)$data * $this->_getRate($row);
            $data = sprintf('%f', $data);

            return $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data);
        }

        return $this->_getValue($row) . '%';
    }
}
