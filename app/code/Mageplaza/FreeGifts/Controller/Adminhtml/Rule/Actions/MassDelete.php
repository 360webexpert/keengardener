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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class MassDelete
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule\Actions
 */
class MassDelete extends Action
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param HelperRule $helperRule
     * @param Session $catalogSession
     */
    public function __construct(
        Context $context,
        HelperRule $helperRule,
        Session $catalogSession
    ) {
        $this->_helperRule = $helperRule;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = $this->_helperRule->getRuleById($ruleId);
        $hasRule = $rule && (int)$rule->getId();
        $gifts = $hasRule ? $rule->getGiftArray() : $this->_catalogSession->getNewGifts();

        $selectedGifts = $this->getRequest()->getParam('mpfreegifts_ids');
        if (count($selectedGifts)) {
            $selectedGifts = array_map('intval', $selectedGifts);
        }

        foreach ($gifts as $id => $gift) {
            if (in_array($id, $selectedGifts, true)) {
                unset($gifts[$id]);
            }
        }

        if ($hasRule) {
            $rule->setGifts(HelperData::jsonEncode($gifts));
            $rule->save();
        } else {
            $this->_catalogSession->setNewGifts($gifts);
        }
    }
}
