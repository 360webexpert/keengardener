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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Controller\Adminhtml\Rule;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\SeoRule\Controller\Adminhtml\Rule;
use Mageplaza\SeoRule\Helper\Data;
use Mageplaza\SeoRule\Model\Rule\Source\Type;
use Mageplaza\SeoRule\Model\RuleFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * @var Js
     */
    protected $backendDecode;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RuleFactory $seoRuleFactory
     * @param Data $helperData
     * @param Js $backendDecode
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RuleFactory $seoRuleFactory,
        Data $helperData,
        Js $backendDecode
    ) {
        $this->backendDecode = $backendDecode;

        parent::__construct($context, $coreRegistry, $seoRuleFactory, $helperData);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if (!empty($data)) {
            try {
                $model = $this->seoRuleFactory->create();
                if (isset($data['rule']['rule_id'])) {
                    $id = $data['rule']['rule_id'];
                    if ($id) {
                        $model->load($id);
                        if ($id != $model->getId()) {
                            throw new LocalizedException(__('The wrong rule is specified.'));
                        }
                    }
                }

                if (isset($data['page'])) {
                    $data['rule']['pages'] = $this->helperData->serialize($this->backendDecode->decodeGridSerializedInput($data['page']));
                }
                if (isset($data['rule']['stores'])) {
                    if (is_array($data['rule']['stores'])) {
                        $data['rule']['stores'] = implode(',', $data['rule']['stores']);
                    }
                }
                $condition = '';
                if (isset($data['rule'])) {
                    if (isset($data['rule']['conditions'])) {
                        $condition = $data['rule']['conditions'];
                        unset($data['rule']['conditions']);
                    }
                    $data                = $data['rule'];
                    $data['conditions']  = $condition;
                    $data['entity_type'] = $this->_getSession()->getSeoRuleType();
                }
                if ($data['entity_type'] == Type::PRODUCTS || $data['entity_type'] == Type::LAYERED_NAVIGATION) {
                    $model->loadPost($data);
                } else {
                    if (isset($data['category_conditions'])) {
                        $data['categorys'] = trim($data['category_conditions']);
                    }
                    $model->addData($data);
                }
                $model->save();
                if ($this->getRequest()->getParam('back')) {
                    $this->messageManager->addSuccess(__('The Rule has been saved'));

                    return $this->_redirect(
                        'seo/rule/edit',
                        ['rule_id' => $model->getId(), 'type' => $model->getEntityType()]
                    );
                }

                $isApply = $this->getRequest()->getParam('option');
                if (!empty($isApply) && $isApply == 'saveAndApply') {
                    $this->helperData->applyRules();
                    $this->messageManager->addSuccess(__('The Rule has been saved and applied.'));
                } else {
                    $this->messageManager->addSuccess(__('The Rule has been saved.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Rule.'));
                $this->messageManager->addException($e, $e->getMessage() . ' - ' . $e->getFile());
            }
        }

        $this->_redirect('seo/*/');
    }
}
