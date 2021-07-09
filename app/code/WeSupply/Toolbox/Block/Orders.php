<?php

namespace WeSupply\Toolbox\Block;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use WeSupply\Toolbox\Helper\Data as Helper;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;

class Orders extends Template
{
    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var WeSupplyApiInterface
     */
    protected $weSupplyApi;

    /**
     * @var string
     */
    private $searchByKey;

    /**
     * @var string
     */
    private $searchByVal;

    /**
     * Orders constructor.
     * @param Context $context
     * @param Helper $helper
     * @param SessionManagerInterface $session
     * @param WeSupplyApiInterface $weSupplyApi
     */
    public function __construct(
        Context $context,
        Helper $helper,
        SessionManagerInterface $session,
        WeSupplyApiInterface $weSupplyApi
    )
    {
        $this->_isScopePrivate = true;

        $this->_helper = $helper;
        $this->_session = $session;
        $this->weSupplyApi = $weSupplyApi;

        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getIframeUrl()
    {
        $this->setSearchParams($this->getAuthSearchBy());
        if (is_null($this->searchByKey) || is_null($this->searchByVal)) {
            return $this->getIframeUri()  . '?platformType=' . $this->_helper->getPlatform();
        }

        if ($this->isFirstLoginAttempt()) {
            return $this->buildAuthUrl();
        }

        return $this->buildOrdersViewUrl();
    }

    /**
     * @return string
     */
    private function getIframeUri()
    {
        if ($this->_session->getSessionAliasDomain()) {
            return 'https://' . trim($this->_session->getSessionAliasDomain(), '/') . '/';
        }

        return $this->_helper->getWesupplyFullDomain();
    }

    /**
     * @return bool
     */
    private function isFirstLoginAttempt()
    {
        if ($this->_session->getFirstAttempt()) {
            $this->_session->unsFirstAttempt();

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getAuthToken()
    {
        return $this->_session->getSessionAuthToken();
    }

    /**
     * @return array
     */
    private function getAuthSearchBy()
    {
        return $this->_session->getSessionAuthSearchBy();
    }

    /**
     * @param $auth
     */
    private function setSearchParams($auth)
    {
        reset($auth);
        // always set only the first key/value pair
        $this->searchByKey = key($auth);
        $this->searchByVal = !is_null($this->searchByKey) ? $auth[$this->searchByKey] : null;
    }

    /**
     * @return string
     */
    private function buildAuthUrl()
    {
        if ($this->searchByKey == 'embedded-em') {
            return $this->getIframeUri() .
                '?token=' . $this->getAuthToken() . '&' . $this->searchByKey . '=' . $this->searchByVal . '&platformType=' . $this->_helper->getPlatform();
        }

        return $this->grabWeSupplyOrderView();
    }

    /**
     * @return string
     */
    private function buildOrdersViewUrl()
    {
        if ($this->searchByKey == 'embedded-em') {
            return $this->getIframeUri() .
                'account/orders?platformType=' . $this->_helper->getPlatform();
        }

        return $this->grabWeSupplyOrderView();
    }

    /**
     * @return string
     */
    private function grabWeSupplyOrderView()
    {
        $this->setApiCredentials();
        if ($orderUrlArr = $this->weSupplyApi->weSupplyInterogation($this->searchByVal)) {
            return reset($orderUrlArr)  . '&platformType=' . $this->_helper->getPlatform();
        }

        return $this->getIframeUri()  . '?platformType=' . $this->_helper->getPlatform();
    }

    /**
     * @return void
     */
    private function setApiCredentials()
    {
        $this->weSupplyApi->setApiPath($this->_helper->getWesupplyApiFullDomain());
        $this->weSupplyApi->setApiClientId($this->_helper->getWeSupplyApiClientId());
        $this->weSupplyApi->setApiClientSecret($this->_helper->getWeSupplyApiClientSecret());
    }
}
