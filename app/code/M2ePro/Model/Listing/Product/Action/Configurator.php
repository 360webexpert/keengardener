<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Listing\Product\Action;

/**
 * Class \Ess\M2ePro\Model\Listing\Product\Action\Configurator
 */
abstract class Configurator extends \Ess\M2ePro\Model\AbstractModel
{
    const MODE_INCLUDING = 'including';
    const MODE_EXCLUDING = 'excluding';

    protected $mode = self::MODE_EXCLUDING;

    protected $allowedDataTypes = [];

    protected $params = [];

    //########################################

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->allowedDataTypes = $this->getAllDataTypes();
    }

    //########################################

    abstract public function getAllDataTypes();

    //########################################

    public function enableAll()
    {
        $this->mode             = self::MODE_EXCLUDING;
        $this->allowedDataTypes = $this->getAllDataTypes();

        return $this;
    }

    public function disableAll()
    {
        $this->mode             = self::MODE_INCLUDING;
        $this->allowedDataTypes = [];

        return $this;
    }

    //########################################

    public function getMode()
    {
        return $this->mode;
    }

    public function isExcludingMode()
    {
        return $this->mode == self::MODE_EXCLUDING;
    }

    public function isIncludingMode()
    {
        return $this->mode == self::MODE_INCLUDING;
    }

    // ---------------------------------------

    public function setModeExcluding()
    {
        $this->mode = self::MODE_EXCLUDING;
        return $this;
    }

    public function setModeIncluding()
    {
        $this->mode = self::MODE_INCLUDING;
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getAllowedDataTypes()
    {
        return $this->allowedDataTypes;
    }

    //########################################

    public function isAllowed($dataType)
    {
        $this->validateDataType($dataType);
        return in_array($dataType, $this->allowedDataTypes);
    }

    public function allow($dataType)
    {
        $this->validateDataType($dataType);

        if ($this->isAllowed($dataType)) {
            return $this;
        }

        $this->allowedDataTypes[] = $dataType;
        return $this;
    }

    public function disallow($dataType)
    {
        $this->validateDataType($dataType);

        if (!$this->isAllowed($dataType)) {
            return $this;
        }

        $this->allowedDataTypes = array_diff($this->allowedDataTypes, [$dataType]);
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    //########################################

    /**
     * @param \Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator
     * @return bool
     */
    public function isDataConsists(\Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator)
    {
        return !array_diff($configurator->getAllowedDataTypes(), $this->getAllowedDataTypes());
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator
     * @return bool
     */
    public function isParamsConsists(\Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator)
    {
        return !array_diff_assoc($configurator->getParams(), $this->getParams());
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator
     * @return $this
     */
    public function mergeData(\Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator)
    {
        if ($configurator->isExcludingMode()) {
            $this->mode = self::MODE_EXCLUDING;
        }

        $this->allowedDataTypes = array_unique(array_merge(
            $this->getAllowedDataTypes(),
            $configurator->getAllowedDataTypes()
        ));

        return $this;
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator
     * @return $this
     */
    public function mergeParams(\Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator)
    {
        $this->params = array_unique(array_merge(
            $this->getParams(),
            $configurator->getParams()
        ));

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getSerializedData()
    {
        return [
            'mode'               => $this->mode,
            'allowed_data_types' => $this->allowedDataTypes,
            'params'             => $this->params,
        ];
    }

    /**
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setUnserializedData(array $data)
    {
        if (!empty($data['mode'])) {
            $this->mode = $data['mode'];
        }

        if (!empty($data['allowed_data_types'])) {
            if (!is_array($data['allowed_data_types']) ||
                array_diff($data['allowed_data_types'], $this->getAllDataTypes())
            ) {
                throw new \Ess\M2ePro\Model\Exception\Logic(
                    'Allowed data types are invalid.',
                    ['allowed_data_types' => $data['allowed_data_types']]
                );
            }

            $this->allowedDataTypes = $data['allowed_data_types'];
        }

        if (!empty($data['params'])) {
            if (!is_array($data['params'])) {
                throw new \InvalidArgumentException('Params has invalid format.');
            }

            $this->params = $data['params'];
        }

        return $this;
    }

    //########################################

    protected function validateDataType($dataType)
    {
        if (!in_array($dataType, $this->getAllDataTypes())) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                'Data type is invalid',
                ['data_type' => $dataType]
            );
        }
    }

    //########################################
}
