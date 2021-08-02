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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Plugin;

use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AbstractPlugin
 * @package Mageplaza\FreeGifts\Plugin
 */
abstract class AbstractPlugin
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * AbstractPlugin constructor.
     *
     * @param HelperRule $helperRule
     */
    public function __construct(
        HelperRule $helperRule
    ) {
        $this->_helperRule = $helperRule;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helperRule->getHelperData()->isEnabled();
    }
}
