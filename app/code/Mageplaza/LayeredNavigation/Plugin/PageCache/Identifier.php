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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\PageCache;

use Magento\Framework\App\Request\Http;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class Identifier
 * @package Mageplaza\LayeredNavigation\Plugin\PageCache
 */
class Identifier
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Identifier constructor.
     *
     * @param Http $request
     * @param Data $helperData
     */
    public function __construct(
        Http $request,
        Data $helperData
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Framework\App\PageCache\Identifier $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $payload = Data::jsonDecode($this->request->getContent());
        $isLayer = isset($payload['mp_layer']) ? $payload['mp_layer'] : false;
        $result .= $isLayer ? 'mplayer' : '';

        return $result;
    }
}
