<?php

namespace WeSupply\Toolbox\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use WeSupply\Toolbox\Helper\Data as Helper;

class Track extends Template
{
    /**
     * @const array
     */
    private const PARAMS_SANITIZER = [
        FILTER_SANITIZE_SPECIAL_CHARS,
        FILTER_SANITIZE_ADD_SLASHES,
        FILTER_SANITIZE_STRING
    ];

    /**
     * @var array
     */
    private $params;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Track constructor.
     * @param Context $context
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        Helper $helper
    )
    {
        $this->params = $context->getRequest()->getParams();
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->helper->getPlatform();
    }

    /**
     * @return bool|mixed
     */
    public function getOrderId()
    {
        $params = $this->getParams();
        if (isset($params['orderID'])) {
            $orderId = $params['orderID'];
            $this->sanitizeParamsVal($orderId);

            return $orderId;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getWeSupplyTrackUrl()
    {
        if ($trackingId = $this->getTrackingCode()) {
            return $this->helper->getWesupplyFullDomain() . 'track/' . $trackingId . '/';
        }

        return $this->helper->getWesupplyFullDomain();
    }

    /**
     * @return bool
     */
    public function canShowEmbedded()
    {
        if (
            $this->helper->getWeSupplyEnabled() &&
            $this->helper->getConnectionStatus()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Return tracking id which should be a param key with empty value
     * @return bool|mixed
     */
    private function getTrackingCode()
    {
        $res = array_filter($this->getParams(), function($val) {
            return $val === '';
        });

        if ($res) {
            $keys = array_keys($res);
            $trackId = reset($keys);

            $this->sanitizeParamsVal($trackId);

            return $trackId ?? false;
        }

        return false;
    }

    /**
     * @param $value
     */
    private function sanitizeParamsVal(&$value)
    {
        foreach (self::PARAMS_SANITIZER as $sanitizer) {
            $value = filter_var($value, $sanitizer);
        }

        $value = preg_replace('/[^\da-z_]/i', '', $value);
    }
}
