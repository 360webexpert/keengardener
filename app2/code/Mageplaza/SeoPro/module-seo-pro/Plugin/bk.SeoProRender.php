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
 * @package     Mageplaza_SeoPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoPro\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;
use Mageplaza\SeoPro\Helper\Data as HelperConfig;

/**
 * Class SeoBeforeRender
 * @package Mageplaza\Seo\Plugin
 */
class SeoProRender
{
    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * SeoProRender constructor.
     *
     * @param PageConfig $pageConfig
     * @param Http $request
     * @param HelperConfig $helperConfig
     * @param UrlInterface $url
     */
    public function __construct(
        PageConfig $pageConfig,
        Http $request,
        HelperConfig $helperConfig,
        UrlInterface $url
    ) {
        $this->pageConfig   = $pageConfig;
        $this->request      = $request;
        $this->helperConfig = $helperConfig;
        $this->url          = $url;
    }

    /**
     * @param Renderer $subject
     * @param $result
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function afterRenderMetadata(Renderer $subject, $result)
    {
        if ($this->helperConfig->isEnableCanonicalUrl()
            && !$this->checkRobotNoIndex()
            && !in_array($this->request->getFullActionName(), $this->helperConfig->getDisableCanonicalPages(), true)
        ) {

            //use router path to get current url without parameters
            $url = $this->url->getUrl('*/*/*', ['_use_rewrite' => true]);

            /**
             * For issue XS Vulnerability
             * $this->safetifyUrl($this->url->getCurrentUrl());
             */
            $this->pageConfig->addRemotePageAsset(
                $url,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        return $result;
    }

    /**
     * Check robot NOINDEX
     * @return bool
     * @throws LocalizedException
     */
    public function checkRobotNoIndex()
    {
        if ($this->helperConfig->isDisableCanonicalUrlWithNoIndexRobots()) {
            $noIndex = explode(',', $this->pageConfig->getRobots());
            if (is_array($noIndex)) {
                return trim($noIndex[0]) === 'NOINDEX';
            }
        }

        return false;
    }

    /**
     * Avoid XS Vulnerability
     * Refer issue: https://github.com/mageplaza/module-core/issues/31
     * $return string
     */
    public function safetifyUrl($url)
    {
        return trim(strip_tags($url));
    }
}
