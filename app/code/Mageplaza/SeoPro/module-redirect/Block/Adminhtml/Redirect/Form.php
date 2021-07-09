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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Block\Adminhtml\Redirect;

use Exception;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\UrlRewrite\Model\OptionProvider;

/**
 * Class Form
 * @package Mageplaza\Redirects\Block\Adminhtml\Redirect
 */
class Form extends Generic
{
    /**
     * @var array
     */
    protected $_allStores = null;

    /**
     * @var bool
     */
    protected $_requireStoresFilter = false;

    /**
     * @var OptionProvider
     */
    protected $optionProvider;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param OptionProvider $optionProvider
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OptionProvider $optionProvider,
        Store $systemStore,
        array $data = []
    ) {
        $this->optionProvider = $optionProvider;
        $this->_systemStore   = $systemStore;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        if ($this->_backendSession->getSeoRedirectDataDeleted() && is_array($this->_backendSession->getSeoRedirectDataDeleted())) {
            $form = $this->_formFactory->create();

            foreach ($this->_backendSession->getSeoRedirectDataDeleted() as $key => $value) {
                if ($key == 0) {
                    $fieldset = $form->addFieldset('base_redirect' . $key, [
                        'legend' => __(
                            'A total of %1 record(s) have been deleted. Do you want to redirect this URL to a new one?',
                            count($this->_backendSession->getSeoRedirectDataDeleted())
                        ),
                        'class'  => 'fieldset-wide'
                    ]);
                } else {
                    $fieldset = $form->addFieldset('base_redirect' . $key, [
                        'legend' => '',
                        'class'  => 'fieldset-wide'
                    ]);
                }

                $this->_prepareStoreElement($fieldset, $key);

                $fieldset->addField('request_path' . $key, 'text', [
                    'label'    => __('Old URL'),
                    'title'    => __('Old URL'),
                    'name'     => 'request_path',
                    'required' => true,
                    'disabled' => true,
                    'readonly' => true,
                    'value'    => $value,
                    'class'    => 'request_path'
                ]);

                $fieldset->addField('target_path' . $key, 'text', [
                    'label'    => __('New Request'),
                    'title'    => __('New Request'),
                    'name'     => 'target_path',
                    'required' => true,
                    'disabled' => false,
                    'value'    => '',
                    'class'    => 'target_path'
                ]);

                $redirectType = $this->optionProvider->toOptionArray();
                unset($redirectType[0]);
                $fieldset->addField('redirect_type' . $key, 'select', [
                    'label'   => __('Redirect Type'),
                    'title'   => __('Redirect Type'),
                    'name'    => 'redirect_type',
                    'options' => $redirectType,
                    'value'   => '',
                    'class'   => 'redirect_type'
                ]);

                $fieldset->addField('description' . $key, 'textarea', [
                    'label' => __('Description'),
                    'title' => __('Description'),
                    'name'  => 'description',
                    'cols'  => 20,
                    'rows'  => 5,
                    'value' => '',
                    'wrap'  => 'soft',
                    'class' => 'description'
                ]);

                $fieldset->addType('redirect', '\Mageplaza\Redirects\Block\Adminhtml\Redirect\Render\Button');
                $fieldset->addField('redirect' . $key, 'redirect', [
                    'label' => '',
                    'title' => __('Redirect'),
                    'value' => __('Redirect'),
                    'class' => __('action-primary seo-redirect')
                ]);
            }

            $this->setForm($form);
        }

        return parent::_prepareForm();
    }

    /**
     * @param $fieldset
     * @param $key
     *
     * @throws LocalizedException
     */
    protected function _prepareStoreElement($fieldset, $key)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'hidden', [
                'name'  => 'store_id',
                'value' => $this->_storeManager->getStore(true)->getId()
            ]);
        } else {
            $storeElement = $fieldset->addField('store_id' . $key, 'select', [
                'label'    => __('Store'),
                'title'    => __('Store'),
                'name'     => 'store_id',
                'required' => true,
                'value'    => '',
                'class'    => 'store_id'
            ]);

            try {
                $stores = $this->_getStoresListRestrictedByEntityStores($this->_getEntityStores());
            } catch (Exception $e) {
                $stores = [];
                $storeElement->setAfterElementHtml($e->getMessage());
            }

            $storeElement->setValues($stores);
            /** @var $renderer Element */
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $storeElement->setRenderer($renderer);
        }
    }

    /**
     * Get request stores
     *
     * @return array
     */
    protected function _getAllStores()
    {
        if ($this->_allStores === null) {
            $this->_allStores = $this->_systemStore->getStoreValuesForForm();
        }

        return $this->_allStores;
    }

    /**
     * Get entity stores
     *
     * @return array
     */
    protected function _getEntityStores()
    {
        return $this->_getAllStores();
    }

    /**
     * Get stores list restricted by entity stores.
     * Stores should be filtered only if custom entity is specified.
     * If we use custom rewrite, all stores are accepted.
     *
     * @param array $entityStores
     *
     * @return array
     */
    private function _getStoresListRestrictedByEntityStores(array $entityStores)
    {
        $stores = $this->_getAllStores();
        if ($this->_requireStoresFilter) {
            foreach ($stores as $i => $store) {
                if (isset($store['value']) && $store['value']) {
                    $found = false;
                    foreach ($store['value'] as $k => $v) {
                        if (isset($v['value']) && in_array($v['value'], $entityStores)) {
                            $found = true;
                        } else {
                            unset($stores[$i]['value'][$k]);
                        }
                    }
                    if (!$found) {
                        unset($stores[$i]);
                    }
                }
            }
        }

        return $stores;
    }
}
