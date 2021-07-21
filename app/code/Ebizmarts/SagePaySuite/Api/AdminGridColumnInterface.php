<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Api;

interface AdminGridColumnInterface
{

    /**
     * @param array $dataSource
     * @param string $index
     * @param string $fieldName
     * @return array
     */
    function prepareColumn(array $dataSource, string $index, string $fieldName) :array;
}
