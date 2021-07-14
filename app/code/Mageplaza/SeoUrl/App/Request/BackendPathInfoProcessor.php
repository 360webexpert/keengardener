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

use Magento\Backend\Helper\Data;
use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\App\RequestInterface;

/**
 * @api
 * @since 100.0.2
 */
class BackendPathInfoProcessor implements PathInfoProcessorInterface
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var StorePathInfoProcessor
     */
    private $_subject;

    /**
     * BackendPathInfoProcessor constructor.
     *
     * @param StorePathInfoProcessor $subject
     * @param Data $helper
     */
    public function __construct(
        StorePathInfoProcessor $subject,
        Data $helper
    ) {
        $this->_helper = $helper;
        $this->_subject = $subject;
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
        $firstPart = $pathParts[0];

        if ($firstPart != $this->_helper->getAreaFrontName()) {
            return $this->_subject->process($request, $pathInfo);
        }

        return $pathInfo;
    }
}
