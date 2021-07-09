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

namespace Mageplaza\Shopbybrand\Block\Link;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\Config\Source\BrandPosition;

/**
 * Class Footer
 * @package Mageplaza\Shopbybrand\Block\Link
 */
class Footer extends Current
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $helper;

    /**
     * Footer constructor.
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);

        $this->helper = $helper;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->canShowBrandLink(BrandPosition::FOOTERLINK)) {
            return '';
        }

        $this->setData([
            'label' => $this->helper->getBrandTitle(),
            'path'  => $this->helper->getRoute()
        ]);

        return parent::_toHtml();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getHref()
    {
        return $this->helper->getBrandUrl();
    }
}
