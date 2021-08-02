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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Model\RuleFactory;

/**
 * Class GridEdit
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
class GridEdit extends Action
{
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var array
     */
    protected $_errors = [];

    /**
     * GridEdit constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param Json $json
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        Json $json
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_json = $json;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $items = $this->getRequest()->getParam('items', []);
        $rule = $this->_ruleFactory->create();
        $hasError = false;

        $ruleNames = array_column($items, 'name');
        foreach ($ruleNames as $ruleName) {
            if (preg_match('/^\s*$/', $ruleName)) {
                return $this->_json->setData([
                    'messages' => [__('Rule name cannot be empty')],
                    'error' => true,
                ]);
            }
        }

        foreach ($items as $item) {
            try {
                $rule->load($item['rule_id'])
                    ->addData($item)
                    ->save();
                $rule->unsetData();
            } catch (Exception $e) {
                $this->_errors[] = $e->getMessage();
                $hasError = true;
            }
        }

        return $this->_json->setData([
            'messages' => $this->_errors,
            'error' => $hasError
        ]);
    }
}
