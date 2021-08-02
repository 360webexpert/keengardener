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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Catalog\Model\Session;
use Magento\Framework\Registry;
use Mageplaza\FreeGifts\Model\Rule;

/**
 * Class AbstractRenderer
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer
 */
abstract class AbstractRenderer extends Text
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * AbstractRenderer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Session $catalogSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $catalogSession,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getGiftArray()
    {
        /** @var Rule $rule */
        $rule = $this->_registry->registry('current_rule');

        if ($rule && (int)$rule->getId()) {
            return $rule->getGiftArray();
        }

        return $this->_catalogSession->getNewGifts();
    }
}
