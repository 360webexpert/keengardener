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

namespace Mageplaza\SeoUrl\Plugin\Filter;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SeoUrl\Helper\Data as UrlHelper;
use Zend_Serializer_Exception;

/**
 * Class Toolbar
 * @package Mageplaza\SeoUrl\Plugin\Filter
 */
class Toolbar
{
    /**
     * @var UrlHelper
     */
    protected $_moduleHelper;

    /**
     * Toolbar constructor.
     *
     * @param UrlHelper $moduleHelper
     */
    public function __construct(UrlHelper $moduleHelper)
    {
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param $result
     *
     * @return bool|mixed|string
     * @throws NoSuchEntityException
     * @throws Zend_Serializer_Exception
     */
    public function afterGetPagerUrl(\Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar, $result)
    {
        return $this->_moduleHelper->encodeFriendlyUrl($result);
    }
}
