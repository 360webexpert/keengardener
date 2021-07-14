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

use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StorePathInfoProcessor
 * @package Mageplaza\SeoUrl\App\Request
 */
class StorePathInfoProcessor implements PathInfoProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Process path info
     *
     * @param RequestInterface $request
     * @param string $pathInfo
     *
     * @return string
     */
    public function process(RequestInterface $request, $pathInfo)
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        $storeCode = $pathParts[0];

        try {
            /** @var StoreInterface $store */
            $store = $this->storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            return $pathInfo;
        }

        if ($store->isUseStoreInUrl()) {
            if (empty($storeCode)) {
                return $pathInfo;
            }

            if (!$request->isDirectAccessFrontendName($storeCode)) {
                $this->storeManager->setCurrentStore($storeCode);
                $pathInfo = '/' . (isset($pathParts[1]) ? $pathParts[1] : '');

                return $pathInfo;
            }

            $request->setActionName('noroute');

            return $pathInfo;
        }

        return $pathInfo;
    }
}
