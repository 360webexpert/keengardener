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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Model\StoreSwitcher;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\HTTP\PhpEnvironment\RequestFactory;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class RewriteUrl
 * @package Mageplaza\Shopbybrand\Plugin\Model\Model\StoreSwitcher
 */
class RewriteUrl
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * RewriteUrl constructor.
     *
     * @param Data $helperData
     * @param RequestFactory $requestFactory
     * @param DecoderInterface $urlDecoder
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        Data $helperData,
        RequestFactory $requestFactory,
        DecoderInterface $urlDecoder,
        EncoderInterface $urlEncoder
    ) {
        $this->helperData = $helperData;
        $this->requestFactory = $requestFactory;
        $this->urlDecoder = $urlDecoder;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * @param StoreSwitcherInterface $subject
     * @param $result
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     *
     * @return string|string|string[]
     */
    public function afterSwitch(
        StoreSwitcherInterface $subject,
        $result,
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ) {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        $targetUrl = $redirectUrl;

        if (strpos($result, 'switchrequest') !== false) {
            /** @var Request $request */
            $request = $this->requestFactory->create(['uri' => $targetUrl]);
            $encodedUrl = (string)$request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED);
            $targetUrl = $this->urlDecoder->decode($encodedUrl);
            $url = $this->processUrl($targetUrl, $targetUrl, $fromStore, $targetStore);
            $uenc = $this->urlEncoder->encode($url);
            $result = str_replace(
                ActionInterface::PARAM_NAME_URL_ENCODED . '=' . $encodedUrl,
                ActionInterface::PARAM_NAME_URL_ENCODED . '=' . $uenc,
                $result
            );
        } else {
            $result = $this->processUrl($targetUrl, $result, $fromStore, $targetStore);
        }

        return $result;
    }

    /**
     * @param string $url
     * @param string $result
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     *
     * @return string|string[]
     */
    public function processUrl(string $url, string $result, $fromStore, $targetStore)
    {
        /** @var Request $request */
        $request = $this->requestFactory->create(['uri' => $url]);

        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix = $this->helperData->getUrlSuffix();
        if ($urlSuffix) {
            $pos = strpos($identifier, $urlSuffix);
            if ($pos) {
                $identifier = substr($identifier, 0, $pos);
            }
        }

        $routePath = explode('/', $identifier);
        $routeSize = count($routePath);

        if ($routeSize > 3) {
            return $result;
        }

        $brandRoute = $this->helperData->getConfigGeneral('route', $fromStore->getId());
        $toBrandRoute = $this->helperData->getConfigGeneral('route', $targetStore->getId());
        if ($urlSuffix) {
            $brandSuffix = strpos($brandRoute, $urlSuffix);
            if ($brandSuffix) {
                $brandRoute = substr($brandRoute, 0, $brandSuffix);
            }
        }
        if ($routePath[0] === $brandRoute && $routePath[0] !== $toBrandRoute) {
            $result = str_replace($routePath[0], $toBrandRoute, $result);
        }

        return $result;
    }
}
