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

namespace Mageplaza\FreeGifts\Model;

use Magento\CatalogRule\Model\Rule\Action\Collection as CatalogActionCollection;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as ItemActionRule;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CatalogCondition;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory as ItemRule;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection as RuleCollection;
use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\SalesRule\Model\Rule as SaleRuleModel;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as CartRule;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as SaleRuleConditionCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as CartActionRule;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Model\ResourceModel\Rule as RuleResource;
use Mageplaza\FreeGifts\Model\Source\Apply as ApplyType;

/**
 * Class Rule
 * @method int getDiscardSubsequentRules()
 * @method mixed getWebsiteId()
 * @method mixed getCustomerGroupIds()
 * @method mixed getGifts()
 * @method mixed getName()
 * @method mixed getType()
 * @method string getFromDate()
 * @method string getToDate()
 * @method mixed getNumberGiftAllowed()
 * @method int getAllowNotice()
 * @method int getUseConfigAllowNotice()
 * @method int getUseConfigNotice()
 * @method mixed getApplyFor()
 * @method string getNotice()
 * @method Rule setWebsiteId(string $websiteId)
 * @method Rule setCustomerGroupIds(string $customerGroupIds)
 * @method Rule setGifts(string $gifts)
 * @method Rule setFromDate(string $fromDate)
 * @method Rule setNumberGiftAllowed(mixed $numberGiftAllowed)
 * @method Rule setNotice(string $notice)
 * @method Rule setAllowNotice(string $config)
 * @method Rule setApplyFor(string $apply)
 * @method Rule setUseConfigAllowNotice(string $config)
 * @method Rule setUseConfigNotice(string $config)
 * @package Mageplaza\FreeGifts\Model
 */
class Rule extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mp_freegifts_rules';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mp_freegifts_rules';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_freegifts_rules';

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @var CartRule
     */
    protected $_cartRule;

    /**
     * @var CartActionRule
     */
    protected $_cartActionRule;

    /**
     * @var ItemRule
     */
    protected $_itemRule;

    /**
     * @var ItemActionRule
     */
    protected $_itemActionRule;

    /**
     * @var SaleRuleModel
     */
    protected $_saleRuleModel;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CartRule $cartRule
     * @param CartActionRule $cartActionRule
     * @param ItemRule $itemRule
     * @param ItemActionRule $itemActionRule
     * @param SaleRuleModel $saleRuleModel
     * @param HelperData $helperData
     * @param Cart $cart
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CartRule $cartRule,
        CartActionRule $cartActionRule,
        ItemRule $itemRule,
        ItemActionRule $itemActionRule,
        SaleRuleModel $saleRuleModel,
        HelperData $helperData,
        Cart $cart,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null
    ) {
        $this->_cartRule = $cartRule;
        $this->_cartActionRule = $cartActionRule;
        $this->_itemRule = $itemRule;
        $this->_itemActionRule = $itemActionRule;
        $this->_saleRuleModel = $saleRuleModel;
        $this->_cart = $cart;
        $this->_helperData = $helperData;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(RuleResource::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * @return CatalogCondition|RuleCombine|SaleRuleModel\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->isItemRule() ? $this->_itemRule->create() : $this->_cartRule->create();
    }

    /**
     * @return CatalogActionCollection|RuleCollection|SaleRuleConditionCombine
     */
    public function getActionsInstance()
    {
        return $this->isItemRule() ? $this->_itemActionRule->create() : $this->_cartActionRule->create();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $this->_saleRuleModel->getConditionsFieldSetId($formName);
    }

    /**
     * @return array
     */
    public function getCustomerGroupArray()
    {
        return explode(',', $this->getCustomerGroupIds());
    }

    /**
     * @return array
     */
    public function getWebsiteArray()
    {
        return explode(',', $this->getWebsiteId());
    }

    /**
     * @return mixed
     */
    public function getGiftArray()
    {
        return HelperData::jsonDecode($this->getGifts());
    }

    /**
     * @return int|void
     */
    public function getMaxGift()
    {
        $giftAllowed = (int)$this->getNumberGiftAllowed();
        $totalGifts = count($this->getGiftArray());

        if ($giftAllowed === 0 || $giftAllowed >= $totalGifts) {
            return $totalGifts;
        }

        return $giftAllowed;
    }

    /**
     * @return bool
     */
    public function isItemRule()
    {
        return $this->getApplyFor() === ApplyType::ITEM;
    }

    /**
     * @return mixed
     */
    public function isAllowNotice()
    {
        if ((int)$this->getUseConfigAllowNotice()) {
            return $this->_helperData->getAllowNotice();
        }

        return (bool)$this->getAllowNotice();
    }

    /**
     * @return mixed|string
     */
    public function getNoticeContent()
    {
        if ((int)$this->getUseConfigNotice()) {
            return $this->_helperData->getNotice();
        }

        return $this->getNotice();
    }
}
