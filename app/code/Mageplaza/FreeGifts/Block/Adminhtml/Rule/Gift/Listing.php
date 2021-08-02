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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Gift;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Gift as GiftGrid;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Product as ProductGrid;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class Listing
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Gift
 */
class Listing extends Widget implements RendererInterface
{
    const TYPE_FREE = 'free';
    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED = 'fixed';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::gift_listing.phtml';

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var ProductGrid
     */
    protected $_productGrid;

    /**
     * @var Yesno
     */
    protected $_yesno;

    /**
     * Listing constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param HelperRule $helperRule
     * @param ProductGrid $productGrid
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        HelperRule $helperRule,
        ProductGrid $productGrid,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_helperRule = $helperRule;
        $this->_productGrid = $productGrid;
        $this->_yesno = $yesNo;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getAddGiftUrl()
    {
        return $this->getUrl('mpfreegifts/rule_actions/addGift', ['form_key' => $this->getFormKey()]);
    }

    /**
     * @return string
     */
    public function getUpdateItemUrl()
    {
        return $this->getUrl('mpfreegifts/rule_actions/updateGift', ['form_key' => $this->getFormKey()]);
    }

    /**
     * @return string
     */
    public function getProductGridUrl()
    {
        return $this->_productGrid->getGridUrl();
    }

    /**
     * @return string
     */
    public function getGridListingHtml()
    {
        $page = $this->_pageFactory->create();

        return $page->getLayout()->createBlock(GiftGrid::class)->toHtml();
    }

    /**
     * @return mixed
     */
    public function getCurrentRuleId()
    {
        return $this->getRequest()->getParam('rule_id');
    }

    /**
     * @return array[]
     */
    public function getDiscountType()
    {
        return [
            ['value' => self::TYPE_FREE, 'label' => __('Free')],
            ['value' => self::TYPE_PERCENT, 'label' => __('Percent')],
            ['value' => self::TYPE_FIXED, 'label' => __('Fixed')],
        ];
    }

    /**
     * @return array[]
     */
    public function getFreeShipOption()
    {
        return $this->_yesno->toOptionArray();
    }

    /**
     * @return string
     */
    public function getSystemConfig()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = $ruleId ? $this->_helperRule->getRuleById($ruleId) : null;
        $systemAllowNotice = $this->_helperRule->getHelperData()->getAllowNotice();
        $systemNotice = $this->_helperRule->getHelperData()->getNotice();
        $systemConfig = [
            '#rule_use_config_allow_notice' => $rule ? $rule->getUseConfigAllowNotice() : 1,
            '#rule_use_config_notice' => $rule ? $rule->getUseConfigNotice() : 1,
            '#rule_allow_notice' => $rule ? $rule->getAllowNotice() : $systemAllowNotice,
            '#rule_notice' => $rule ? $rule->getNotice() : $systemNotice,
        ];

        return HelperData::jsonEncode($systemConfig);
    }
}
