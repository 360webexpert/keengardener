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

namespace Mageplaza\FreeGifts\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\FreeGifts\Block\Cart\CartRule;

/**
 * Class Data
 * @package Mageplaza\FreeGifts\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpfreegifts';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param TimezoneInterface $timezone
     * @param PriceHelper $priceHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        TimezoneInterface $timezone,
        PriceHelper $priceHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_customerSession = $customerSession;
        $this->_timezone = $timezone;
        $this->_priceHelper = $priceHelper;
        $this->_priceCurrency = $priceCurrency;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCustomerGroupId()
    {
        return (string)$this->_customerSession->getCustomerGroupId();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return string
     */
    public function getCurrentDate()
    {
        $timeStamp = $this->_timezone->scopeTimeStamp();

        return date('Y-m-d', $timeStamp);
    }

    /**
     * @param float|string $price
     *
     * @return mixed
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * @param int|float $price
     *
     * @return float
     * @throws NoSuchEntityException
     */
    public function formatAdminPrice($price)
    {
        $code = $this->storeManager->getStore()->getBaseCurrencyCode();

        return $this->_priceCurrency->format($price, false, PriceCurrencyInterface::DEFAULT_PRECISION, null, $code);
    }

    /**
     * @return array|mixed
     */
    public function getDefaultCountry()
    {
        return $this->getConfigValue('general/country/default');
    }

    /**
     * @return bool
     */
    public function hasCartRule()
    {
        $cartRule = $this->objectManager->create(CartRule::class);

        return $cartRule->getValidatedCartRules() ? true : false;
    }

    //////////////////////////////////////////////////////////////
    // General Configuration
    //////////////////////////////////////////////////////////////

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGiftLayout($storeId = null)
    {
        return $this->getConfigGeneral('gift_layout', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAllowNotice($storeId = null)
    {
        return $this->getConfigGeneral('allow_notice', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getNotice($storeId = null)
    {
        return $this->getConfigGeneral('notice', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHideNotification($storeId = null)
    {
        return $this->getConfigGeneral('hide_notification', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGiftIcon($storeId = null)
    {
        $gift = $this->getConfigGeneral('icon', $storeId);
        if ($gift) {
            $baseMedia = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);

            return $baseMedia . 'mageplaza/freegifts/' . $gift;
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getGiftTemplate($type)
    {
        return 'Mageplaza_FreeGifts/' . $type . '/' . $this->getGiftLayout();
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getAllowReminder($storeId = null)
    {
        return $this->getConfigGeneral('allow_reminder', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed|string|string[]
     */
    public function getNotifyMessage($storeId = null)
    {
        $message = $this->getConfigGeneral('notify_message', $storeId);
        preg_match('({url checkout\/cart})', $message, $matchPattern);

        if ($matchPattern) {
            preg_match('(checkout\/cart)', $matchPattern[0], $route);
            $message = str_replace($matchPattern[0], $this->_getUrl($route[0]), $message);
        }

        return $message;
    }

    //////////////////////////////////////////////////////////////
    // Button Display
    //////////////////////////////////////////////////////////////

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCartPage($storeId = null)
    {
        return $this->getModuleConfig('display/cart_page', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCartItem($storeId = null)
    {
        return $this->getModuleConfig('display/cart_item', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getProductPage($storeId = null)
    {
        return $this->getModuleConfig('display/product_page', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnablePopup($storeId = null)
    {
        return $this->getModuleConfig('display/enable_popup', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getAutoPopup($storeId = null)
    {
        return $this->getModuleConfig('display/auto_popup', $storeId);
    }

    //////////////////////////////////////////////////////////////
    // Button Design
    //////////////////////////////////////////////////////////////

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getButtonLabel($storeId = null)
    {
        return $this->getModuleConfig('design/label', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getButtonColor($storeId = null)
    {
        return $this->getModuleConfig('design/background_color', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTextColor($storeId = null)
    {
        return $this->getModuleConfig('design/text_color', $storeId);
    }
}
