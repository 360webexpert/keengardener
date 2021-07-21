<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class Fraud extends Column
{
    /**
     * @var \Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\FraudColumn
     */
    private $fraudColumn;

    /**
     * Fraud constructor.
     * @param FraudColumn $fraudColumn
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FraudColumn $fraudColumn,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->fraudColumn = $fraudColumn;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getFieldName();
        return $this->fraudColumn->prepareColumn($dataSource, "fraudcode", $fieldName);
    }

    public function getFieldName()
    {
        return $this->getData('name');
    }
}
