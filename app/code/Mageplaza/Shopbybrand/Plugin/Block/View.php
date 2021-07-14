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

namespace Mageplaza\Shopbybrand\Plugin\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Page\Config;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class View
 * @package Mageplaza\Shopbybrand\Plugin\Block
 */
class View
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * View constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->_request = $request;
    }

    /**
     * @param Config $config
     * @param $url
     * @param $contentType
     * @param array $properties
     * @param null $name
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeAddRemotePageAsset(Config $config, $url, $contentType, array $properties = [], $name = null)
    {
        if (!$this->helperData->isEnabled() || $this->_request->getFullActionName() !== 'mpbrand_index_view') {
            return [$url, $contentType, $properties, $name];
        }
        if ($contentType === 'canonical') {
            $brand = $this->helperData->getBrand();
            $url = $this->helperData->getBrandUrl($brand);
        }

        return [$url, $contentType, $properties, $name];
    }
}
