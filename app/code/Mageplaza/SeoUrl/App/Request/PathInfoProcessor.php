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
 * @package     Mageplaza_SeoUrl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoUrl\App\Request;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SeoUrl\Helper\Data;
use Zend_Serializer_Exception;

/**
 * Class PathInfoProcessor
 * @package Mageplaza\SeoUrl\App\Request
 */
class PathInfoProcessor extends StorePathInfoProcessor
{
    /**
     * @type Data
     */
    protected $helper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->helper = $helper;

        parent::__construct($storeManager);
    }

    /**
     * Process path info
     *
     * @param RequestInterface $request
     * @param string $pathInfo
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws Zend_Serializer_Exception
     */
    public function process(RequestInterface $request, $pathInfo)
    {
        $pathInfo = parent::process($request, $pathInfo);
        $decodeUrl = $this->helper->decodeFriendlyUrl($pathInfo);

        if (!$decodeUrl) {
            return $pathInfo;
        }

        $requestUri = $request->getRequestUri();
        $requestUri .= strpos($requestUri, '?') ? '&' : '?';
        foreach ($decodeUrl['params'] as $key => $param) {
            $requestUri .= $key . '=' . $param . '&';
        }
        $request->setRequestUri(trim($requestUri, '&'));

        return $decodeUrl['pathInfo'];
    }
}
