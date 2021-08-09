<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Marketplace\Edit;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Ebay\Marketplace\Edit\Form
 */
class Form extends AbstractForm
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->css->addFile('marketplace/form.css');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        foreach ($this->groups as $group) {
            $fieldset = $form->addFieldset(
                'marketplaces_group_'.$group['id'],
                ['legend' => $this->__($group['title'])]
            );

            foreach ($group['marketplaces'] as $marketplace) {

                $display = '';
                if ($marketplace['instance']->getStatus() == \Ess\M2ePro\Model\Marketplace::STATUS_DISABLE) {
                    $display = 'display: none;';
                }

                $afterElementHtml = <<<HTML
<div id="run_single_button_{$marketplace['instance']->getId()}" class="control-value" style="{$display}">
HTML;

                $afterElementHtml .= $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Widget\Button::class)
                    ->setData([
                        'label'   => $this->__('Update Now'),
                        'onclick' => 'MarketplaceObj.runSingleSynchronization(this)',
                        'class' => 'run_single_button primary'
                    ])->toHtml();

                $afterElementHtml .= <<<HTML
                </div>
                <div id="synch_info_container" class="control-value">
                    <div id="synch_info_wait_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: gray;">&nbsp; {$this->__('Waiting')}</div>

                    <div id="synch_info_process_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: blue;">&nbsp; {$this->__('Processing')}</div>

                    <div id="synch_info_complete_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: green;">{$this->__('Completed')}</div>

                    <div id="synch_info_error_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: red;">{$this->__('Error')}</div>

                    <div id="synch_info_skip_{$marketplace['instance']->getId()}"
                        class="value" style="display: none; color: gray;">{$this->__('Skipped')}</div>

                    <div id="marketplace_title_{$marketplace['instance']->getId()}"
                        class="value" style="display: none;">{$marketplace['instance']->getTitle()}</div>
                </div>
                <div id="changed_{$marketplace['instance']->getId()}" class="changed control-value"
                    style="display: none;">
                </div>
HTML;

                $selectData = [
                    'label' => $this->__($marketplace['instance']->getData('title')),
                    'style' => 'display: inline-block;',
                    'after_element_html' => $afterElementHtml
                ];

                if ($marketplace['params']['locked'] || $marketplace['params']['lockedByPickupStore']) {
                    $lockedText = '';
                    if ($marketplace['params']['locked']) {
                        $lockedText = $this->__('Used in Listing(s)');
                    } elseif ($marketplace['params']['lockedByPickupStore']) {
                        $lockedText = $this->__('Used In-Store Pickup.');
                    }

                    $selectData['disabled'] = 'disabled';
                    $selectData['values'] = [
                        \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE => $this->__('Enabled') . ' - ' . $lockedText
                    ];
                    $selectData['value'] = \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE;
                } else {
                    $selectData['values'] = [
                        \Ess\M2ePro\Model\Marketplace::STATUS_DISABLE => $this->__('Disabled'),
                        \Ess\M2ePro\Model\Marketplace::STATUS_ENABLE => $this->__('Enabled')
                    ];
                    $selectData['value'] = $marketplace['instance']->getStatus();
                }

                $selectData['name'] = 'status_'.$marketplace['instance']->getId();
                $selectData['class'] = 'marketplace_status_select';
                $selectData['note'] = $marketplace['instance']->getUrl();

                $fieldset->addField(
                    'status_'.$marketplace['instance']->getId(),
                    self::SELECT,
                    $selectData
                )->addCustomAttribute('marketplace_id', $marketplace['instance']->getId())
                 ->addCustomAttribute('component_name', \Ess\M2ePro\Helper\Component\Ebay::NICK)
                 ->addCustomAttribute('component_title', $this->getHelper('Component\Ebay')->getTitle())
                 ->addCustomAttribute('onchange', 'MarketplaceObj.changeStatus(this);');
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        /** @var \Ess\M2ePro\Model\Marketplace $tempMarketplaces */
        $tempMarketplaces = $this->parentFactory->getObject(\Ess\M2ePro\Helper\Component\Ebay::NICK, 'Marketplace')
            ->getCollection()
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC')
            ->getItems();

        $storedStatuses = [];
        $groups = [];
        $idGroup = 1;

        $groupOrder = [
            'america'      => 'America',
            'europe'       => 'Europe',
            'asia_pacific' => 'Asia / Pacific',
            'other'        => 'Other'
        ];

        foreach ($groupOrder as $key => $groupOrderTitle) {
            $groups[$key] = [
                'id'           => $idGroup++,
                'title'        => $groupOrderTitle,
                'marketplaces' => []
            ];

            foreach ($tempMarketplaces as $tempMarketplace) {
                if ($groupOrderTitle != $tempMarketplace->getGroupTitle()) {
                    continue;
                }

                $isLocked = (bool)$this->activeRecordFactory->getObject('Listing')->getCollection()
                    ->addFieldToFilter('marketplace_id', $tempMarketplace->getId())
                    ->getSize();

                $isLockedByPickupStore = false;

                if ($this->getHelper('Component_Ebay_PickupStore')->isFeatureEnabled()) {
                    $isLockedByPickupStore = (bool)$this->activeRecordFactory->getObject('Ebay_Account_PickupStore')
                        ->getCollection()
                        ->addFieldToFilter('marketplace_id', $tempMarketplace->getId())
                        ->getSize();
                }

                $storedStatuses[] = [
                    'marketplace_id' => $tempMarketplace->getId(),
                    'status'         => $tempMarketplace->getStatus()
                ];

                /** @var $tempMarketplace \Ess\M2ePro\Model\Marketplace */
                $marketplace = [
                    'instance' => $tempMarketplace,
                    'params'   => [
                        'locked' => $isLocked,
                        'lockedByPickupStore' => $isLockedByPickupStore
                    ]
                ];

                $groups[$key]['marketplaces'][] = $marketplace;
            }
        }

        $this->groups = $groups;
        $this->storedStatuses = $storedStatuses;
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $this->jsUrl->addUrls([
            'formSubmit' => $this->getUrl('*/ebay_marketplace/save'),
            'logViewUrl' => $this->getUrl(
                '*/ebay_synchronization_log/index',
                ['back'=>$this->getHelper('Data')->makeBackUrlParam('*/ebay_synchronization/index')]
            ),
            'runSynchNow' => $this->getUrl('*/ebay_marketplace/runSynchNow'),
        ]);

        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Ebay\Marketplace'));
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Ebay\Category'));

        $this->jsTranslator->addTranslations([

            'Some eBay Categories were deleted from eBay. Click <a target="_blank" href="%url%">here</a> to check.' =>
                $this->__(
                    'Some eBay Categories were deleted from eBay.
                 Click <a target="_blank" href="%url%">here</a> to check.'
                )
        ]);

        $storedStatuses = $this->getHelper('Data')->jsonEncode($this->storedStatuses);
        $this->js->addOnReadyJs(<<<JS
            require([
                'M2ePro/Marketplace',
                'M2ePro/Ebay/Marketplace/SynchProgress',
                'M2ePro/Plugin/ProgressBar',
                'M2ePro/Plugin/AreaWrapper'
            ], function() {
                window.MarketplaceProgressObj = new EbayMarketplaceSynchProgress(
                    new ProgressBar('marketplaces_progress_bar'),
                    new AreaWrapper('marketplaces_content_container')
                );
                window.MarketplaceObj = new Marketplace(MarketplaceProgressObj, $storedStatuses);
            });
JS
        );

        return parent::_toHtml();
    }

    //########################################
}
