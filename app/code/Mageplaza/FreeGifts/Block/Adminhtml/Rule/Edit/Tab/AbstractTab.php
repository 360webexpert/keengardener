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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Website;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Gift\Listing as GiftListing;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Rule;
use Mageplaza\FreeGifts\Model\Source\Status;
use Mageplaza\FreeGifts\Model\Source\Type;

/**
 * Class AbstractTab
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Edit\Tab
 */
abstract class AbstractTab extends Generic implements TabInterface
{
    const RENDERER_TEMPLATE = 'Magento_CatalogRule::promo/fieldset.phtml';

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var Website
     */
    protected $_websites;

    /**
     * @var Fieldset
     */
    protected $_fieldset;

    /**
     * @var Conditions
     */
    protected $_conditions;

    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var Yesno
     */
    protected $_yesno;

    /**
     * @var GiftListing
     */
    protected $_giftListing;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var CustomerGroupCollection
     */
    protected $_customerGroup;

    /**
     * AbstractTab constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Status $status
     * @param Website $website
     * @param Fieldset $fieldset
     * @param Conditions $conditions
     * @param Type $type
     * @param Yesno $yesno
     * @param GiftListing $giftListing
     * @param HelperRule $helperRule
     * @param CustomerGroupCollection $customerGroup
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $status,
        Website $website,
        Fieldset $fieldset,
        Conditions $conditions,
        Type $type,
        Yesno $yesno,
        GiftListing $giftListing,
        HelperRule $helperRule,
        CustomerGroupCollection $customerGroup,
        array $data = []
    ) {
        $this->_status = $status;
        $this->_websites = $website;
        $this->_fieldset = $fieldset;
        $this->_conditions = $conditions;
        $this->_type = $type;
        $this->_yesno = $yesno;
        $this->_giftListing = $giftListing;
        $this->_helperRule = $helperRule;
        $this->_customerGroup = $customerGroup;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Rule
     */
    public function getCurrentRule()
    {
        return $this->_coreRegistry->registry('current_rule');
    }

    /**
     * @return mixed
     */
    public function getRuleId()
    {
        return $this->getRequest()->getParam('rule_id');
    }

    /**
     * @param string $fieldsetId
     * @param string $formName
     * @param string $prefix
     *
     * @return string
     */
    public function getNewChildUrl($fieldsetId, $formName, $prefix)
    {
        $url = 'mpfreegifts/rule_actions/newHtml/form/' . $fieldsetId;

        return $this->getUrl($url, ['form_namespace' => $formName, 'prefix' => $prefix]);
    }

    /**
     * @return mixed
     */
    public function getApplyFor()
    {
        return $this->getRequest()->getParam('apply');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
