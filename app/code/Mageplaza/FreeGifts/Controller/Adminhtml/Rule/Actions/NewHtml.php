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

use Magento\Rule\Model\Condition\AbstractCondition;
use Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

/**
 * Class NewHtml
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Condition
 */
class NewHtml extends Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $condition = $this->getCondition();
        $html = '';
        $model = $this->_objectManager->create($condition['type'])
            ->setId($condition['id'])
            ->setType($condition['type'])
            ->setRule($this->_ruleFactory->create())
            ->setPrefix($condition['prefix']);

        if ($condition['type_attribute']) {
            $model->setAttribute($condition['type_attribute']);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($condition['form']);
            $model->setFormName($condition['namespace']);
            $html = $model->asHtmlRecursive();
        }

        $this->getResponse()->setBody($html);
    }

    /**
     * @return array
     */
    public function getCondition()
    {
        $params = $this->getRequest()->getParams();
        $typeArray = explode('|', str_replace('-', '/', $params['type']));

        return [
            'id' => $params['id'],
            'namespace' => $params['form_namespace'],
            'form' => $params['form'],
            'type' => $typeArray[0],
            'type_attribute' => !empty($typeArray[1]) ? $typeArray[1] : null,
            'prefix' => $params['prefix'],
        ];
    }
}
