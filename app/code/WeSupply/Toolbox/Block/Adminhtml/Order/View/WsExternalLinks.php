<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Backend\Model\Auth\Session as AuthSession;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Helper\Data as WsHelper;

/**
 * Class WsExternalLinks
 * @package WeSupply\Toolbox\Block\Adminhtml\Order\View
 */
class WsExternalLinks extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var AuthSession
     */

    /**
     * @var WeSupplyApiInterface
     */
    protected $weSupplyApi;

    /**
     * @var WsHelper
     */
    private $_helper;

    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * WsExternalLinks constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param AuthSession $authSession
     * @param WeSupplyApiInterface $weSupplyApi
     * @param WsHelper $_helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        AuthSession $authSession,
        WeSupplyApiInterface $weSupplyApi,
        WsHelper $_helper,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->authSession = $authSession;
        $this->weSupplyApi = $weSupplyApi;
        $this->_helper = $_helper;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function canShowButton()
    {
        if (
            $this->_helper->getWeSupplyEnabled() &&
            ($this->canShowViewOrder() || $this->canShowReturnsList())
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return mixed
     */
    public function canShowViewOrder()
    {
        return $this->_helper->getEnableWeSupplyAdminOrder();
    }

    /**
     * @return mixed
     */
    public function canShowReturnsList()
    {
        return $this->_helper->getEnableWeSupplyAdminReturns();
    }

    /**
     * @return string
     */
    public function getWsOrderViewUrl()
    {
        return $this->_helper->getProtocol() . '://' .
            $this->_helper->getWeSupplySubDomain() . '.admin.' .
            $this->_helper->getWesupplyDomainDefault() . '/' .
            $this->getWsAdminKey() .
            '/admin/order/mage_' . $this->getOrderId();
    }

    /**
     * @return string
     */
    public function getWsReturnsListUrl()
    {
        return $this->_helper->getProtocol() . '://' .
               $this->_helper->getWeSupplySubDomain() . '.admin.' .
               $this->_helper->getWesupplyDomainDefault() . '/' .
               $this->getWsAdminKey() .
               '/admin?view=returns&orderid=' . $this->getOrderIncrementId();
    }

    /**
     * @return mixed
     */
    protected function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return mixed
     */
    protected function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * @return string
     */
    protected function getWsAdminKey()
    {
        if (!$this->authSession->getWsAdminKey() || empty($this->authSession->getWsAdminKey())) {
            $this->setApiConnectionDetails();
            $wsAdminKey = $this->weSupplyApi->getWeSupplyAdminKey();
            if ($wsAdminKey !== FALSE && isset($wsAdminKey['key'])) {
                $this->authSession->setWsAdminKey($wsAdminKey['key']);
            }
        }

        return $this->authSession->getWsAdminKey() ?? '';
    }

    /**
     * @return mixed
     */
    private function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Set WeSupply API credentials
     */
    private function setApiConnectionDetails()
    {
        $this->weSupplyApi->setProtocol($this->_helper->getProtocol());
        $this->weSupplyApi->setApiPath($this->_helper->getWesupplyApiFullDomain());
        $this->weSupplyApi->setApiClientId($this->_helper->getWeSupplyApiClientId());
        $this->weSupplyApi->setApiClientSecret($this->_helper->getWeSupplyApiClientSecret());
    }
}
