<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


declare(strict_types=1);

namespace Amasty\ShippingTableRates\Plugin\ImportExport\Block\Adminhtml\Import\Edit;

use Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\ImportExport\Block\Adminhtml\Import\Edit\Form as ImportExportForm;

class Form
{
    const ALLOWED_ACTIONS = ['amstrates_import_rateimport', 'adminhtml_import_index'];

    /**
     * @var CollectionFactory
     */
    private $methodsCollection;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        CollectionFactory $methodsCollection,
        RequestInterface $request
    ) {
        $this->methodsCollection = $methodsCollection;
        $this->request = $request;
    }

    /**
     * @return array
     */
    private function getSelectOptions(): array
    {
        $methods = [];

        $methodsCollection = $this->methodsCollection->create();
        foreach ($methodsCollection->hashMethodsName() as $id => $name) {
            $methods[$id] = __('(id:%1) %2', $id, $name);
        }

        return $methods;
    }

    /**
     * @param ImportExportForm $subject
     *
     * @return void
     */
    public function beforeGetFormHtml(ImportExportForm $subject)
    {
        if (in_array($this->request->getFullActionName(), self::ALLOWED_ACTIONS)
            && $subject->getForm()->getElement('amastratebasic_behavior_fieldset')
            && !$subject->getForm()->getElement('amastrate_methods')
        ) {
            $methods = $this->getSelectOptions();

            $fieldset = $subject->getForm()->addFieldset(
                'amastrate_methods',
                ['legend' => __('Shipping Table Rate Methods')],
                'amastratebasic_behavior_fieldset'
            );

            $fieldset->addField(
                'amastrate_method',
                'select',
                [
                    'name' => 'amastrate_method',
                    'title' => __('Shipping Table Rate Method'),
                    'label' => __('Shipping Table Rate Method'),
                    'required' => true,
                    'values' => $methods
                ]
            );
        }
    }
}
