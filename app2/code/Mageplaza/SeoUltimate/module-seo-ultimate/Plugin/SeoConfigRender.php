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
 * @package     Mageplaza_SeoUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoUltimate\Plugin;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\SeoUltimate\Helper\Data as HelperConfig;

/**
 * Class SeoHrefLang
 * @package Mageplaza\SeoUltimate\Plugin
 */
class SeoConfigRender
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * SeoConfigRender constructor.
     *
     * @param Http $request
     * @param HelperConfig $helperConfig
     */
    function __construct(
        Http $request,
        HelperConfig $helperConfig
    ) {
        $this->request      = $request;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param Field $subject
     * @param callable $proceed
     * @param AbstractElement $element
     *
     * @return mixed
     */
    public function aroundRender(
        Field $subject,
        callable $proceed,
        AbstractElement $element
    ) {
        $result  = $proceed($element);
        $params  = $this->request->getParams();
        $storeId = (int) $this->request->getParam('store', 0);
        if (isset($params['section']) && $params['section'] == 'seo' && $storeId >= 1) {
            $isDefaultLocale = $this->helperConfig->isUseDefaultLocale();
            if ($isDefaultLocale && ($element->getHtmlId() == 'seo_hreflang_code')) {
                return '';
            }
        }

        return $result;
    }
}
