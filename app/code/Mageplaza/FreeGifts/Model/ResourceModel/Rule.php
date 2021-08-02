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

namespace Mageplaza\FreeGifts\Model\ResourceModel;

use Magento\Catalog\Model\Session;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Rule\Model\ResourceModel\AbstractResource;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Model\Rule as RuleModel;

/**
 * Class Rule
 * @package Mageplaza\FreeGifts\Model\ResourceModel
 */
class Rule extends AbstractResource
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param Session $catalogSession
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        Session $catalogSession,
        $connectionName = null
    ) {
        $this->_helperData = $helperData;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_freegifts_rules', 'rule_id');
    }

    /**
     * @param AbstractModel|RuleModel $object
     *
     * @return $this|AbstractResource
     */
    public function _beforeSave(AbstractModel $object)
    {
        if (is_array($object->getCustomerGroupIds())) {
            $object->setCustomerGroupIds(implode(',', $object->getCustomerGroupIds()));
        }

        if (is_array($object->getWebsiteId())) {
            $object->setWebsiteId(implode(',', $object->getWebsiteId()));
        }

        if ((int)$object->getId() === 0 && !empty($this->_catalogSession->getNewGifts())) {
            $object->setGifts(HelperData::jsonEncode($this->_catalogSession->getNewGifts()));
        }

        if ($object->getFromDate() === '') {
            $object->setFromDate($this->_helperData->getCurrentDate());
        }

        if (trim($object->getNotice()) === '') {
            $object->setNotice(__('You deserve it!'));
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractResource
     */
    public function _afterSave(AbstractModel $object)
    {
        $this->_catalogSession->unsNewGifts();
        $this->_catalogSession->unsGiftIds();

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel|RuleModel $object
     *
     * @return AbstractResource
     */
    public function _afterLoad(AbstractModel $object)
    {
        if ((int)$object->getUseConfigAllowNotice()) {
            $object->setAllowNotice($this->_helperData->getAllowNotice());
        }

        if ((int)$object->getUseConfigNotice()) {
            $object->setNotice($this->_helperData->getNotice());
        }

        return parent::_afterLoad($object);
    }
}
