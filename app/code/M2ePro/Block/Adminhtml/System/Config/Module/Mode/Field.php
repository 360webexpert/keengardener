<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\System\Config\Module\Mode;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\System\Config\Module\Mode\Field
 */
class Field extends \Ess\M2ePro\Block\Adminhtml\System\Config\Integration
{
    //########################################

    /**
     * @inheritdoc
     */

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isModuleDisabled = (int)$this->moduleHelper->isDisabled();
        $buttonHtml = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData([
            'label' => $isModuleDisabled ? 'Enable' : 'Disable',
            'class' => 'action-primary',
            'onclick' => 'toggleM2EProModuleStatus()',
            'style' => 'margin-left: 15px;'
        ])->toHtml();
        $buttonText = 'Module Interface and Automatic Synchronization';

        if ($isModuleDisabled) {
            $title = 'Confirmation';
            $confirmContent = 'Are you sure ?';
            $confirmBtn = 'Ok';
        } else {
            $title = 'Disable Module';
            $confirmContent = <<<HTML
                <p>In case you confirm the Module disabling, the M2E Pro dynamic tasks run by
                Cron will be stopped and the M2E Pro Interface will be blocked.</p>

                <p><b>Note</b>: You can re-enable it anytime you would like by clicking on the <strong>Proceed</strong>
                button for <strong>Enable Module and Automatic Synchronization</strong> option.</p>
HTML;
            $confirmBtn = 'Confirm';
        }

        $toolTip = $this->getTooltipHtml(
            'Inventory and Order synchronization stops. The Module interface becomes unavailable.'
        );

        $html = <<<HTML
<td class="value" colspan="3" style="padding: 2.2rem 1.5rem 0 0;">
    <div style="text-align: left">
        {$buttonText} {$buttonHtml}
        <input id="m2epro_module_mode_field" type="hidden"
            name="groups[module_mode][fields][module_mode_field][value]" value="{$isModuleDisabled}">
        <span style="padding-left: 10px;">{$toolTip}</span>    
    </div>
</td>

<div id="module_mode_confirmation_popup" style="display: none">
{$confirmContent}
</div>

<script>
    require([
        'Magento_Ui/js/modal/confirm'
    ], function(confirm) {
        toggleM2EProModuleStatus = function () {
            confirm({
                title: '{$title}',
                content: jQuery('#module_mode_confirmation_popup').html(),
                buttons: [{
                    text: 'Cancel',
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: '{$confirmBtn}',
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }],
                actions: {
                    confirm: function () {
                        jQuery('#m2epro_module_mode_field').val(+!{$isModuleDisabled});
                        jQuery('#save').trigger('click');
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        }
    });
</script>
HTML;
        return $this->_decorateRowHtml($element, $html);
    }

    //########################################
}
