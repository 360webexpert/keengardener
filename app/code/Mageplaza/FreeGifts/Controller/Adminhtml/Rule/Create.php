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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

/**
 * Class Create
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class Create extends Rule
{

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_catalogSession->unsNewGifts();
        $this->_catalogSession->unsGiftIds();
        $this->_forward('edit');
    }
}
