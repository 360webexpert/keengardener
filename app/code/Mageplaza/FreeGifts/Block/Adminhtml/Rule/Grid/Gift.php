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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;
use Magento\Store\Model\Store;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer\DiscountType;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer\EditAction;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer\FreeShipping;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer\GiftPrice;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Rule;

/**
 * Class Gift
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid
 */
class Gift extends Extended
{
    /**
     * @var ProductCollectionFactory
     */
    protected $_productColFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * Gift constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductCollectionFactory $productColFactory
     * @param Registry $registry
     * @param Session $catalogSession
     * @param HelperRule $helperRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductCollectionFactory $productColFactory,
        Registry $registry,
        Session $catalogSession,
        HelperRule $helperRule,
        array $data = []
    ) {
        $this->_productColFactory = $productColFactory;
        $this->_registry = $registry;
        $this->_catalogSession = $catalogSession;
        $this->_helperRule = $helperRule;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mpfreegifts_gift_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setIsCollapsed(false);

        if ($ruleId = $this->getRequest()->getParam('rule_id')) {
            $this->_registry->unregister('current_rule');
            $this->_registry->register('current_rule', $this->_helperRule->getRuleById($ruleId));
        }
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $giftIds = [];
        $rule = $this->getCurrentRule();
        $newGifts = $this->_catalogSession->getNewGifts();
        if ($rule && $rule->getId()) {
            $giftIds = array_keys($rule->getGiftArray());
        } elseif (is_array($newGifts) && count($newGifts)) {
            $giftIds = array_keys($newGifts);
        }

        $collection = $this->_productColFactory->create();
        $collection->addStoreFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('entity_id', ['in' => $giftIds]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpfreegifts/rule_actions/gift', [
            'form_key' => $this->getFormKey(),
            'rule_id' => $this->getCurrentRule() ? $this->getCurrentRule()->getId() : '',
        ]);
    }

    /**
     * @param object $row
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return $this|Extended
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('mpfreegifts_ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('mpfreegifts/rule_actions/massDelete', [
                    'rule_id' => $this->getCurrentRule() ? $this->getCurrentRule()->getId() : ''
                ]),
                'confirm' => __('Are you sure to delete selected items?'),
                'complete' => 'reloadGiftListing'
            ]
        );

        return $this;
    }

    /**
     * @return Rule
     */
    public function getCurrentRule()
    {
        return $this->_registry->registry('current_rule');
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore();

        $this->addColumn('entity_id', [
            'header' => __('Product ID'),
            'type' => 'number',
            'index' => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index' => 'name',
            'type' => 'text',
            'sortable' => true
        ]);
        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index' => 'sku',
            'type' => 'text',
            'sortable' => true
        ]);
        $this->addColumn('price', [
            'header' => __('Original Price'),
            'column_css_class' => 'price',
            'type' => 'currency',
            'currency_code' => $store->getBaseCurrencyCode(),
            'index' => 'price',
            'renderer' => Price::class
        ]);
        $this->addColumn('discount_type', [
            'filter' => false,
            'sortable' => false,
            'header' => __('Discount Type'),
            'renderer' => DiscountType::class,
            'type' => 'text',
            'index' => 'discount_type'
        ]);
        $this->addColumn('gift_price', [
            'filter' => false,
            'sortable' => false,
            'header' => __('Gift Price'),
            'column_css_class' => 'price',
            'type' => 'currency',
            'currency_code' => $store->getBaseCurrencyCode(),
            'index' => 'gift_price',
            'renderer' => GiftPrice::class
        ]);
        $this->addColumn('free_shipping', [
            'filter' => false,
            'sortable' => false,
            'header' => __('Free Shipping'),
            'renderer' => FreeShipping::class,
            'type' => 'text',
            'index' => 'free_shipping'
        ]);
        $this->addColumn('edit_action', [
            'filter' => false,
            'sortable' => false,
            'header' => __('Action'),
            'renderer' => EditAction::class,
            'type' => 'text',
            'index' => 'edit_action'
        ]);

        return parent::_prepareColumns();
    }
}
