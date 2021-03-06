<?php
/**
 * @author Mapp Digital
 * @copyright Copyright (c) 2021 Mapp Digital US, LLC (https://www.mapp.com)
 * @package MappDigital_Cloud
 */
namespace MappDigital\Cloud\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MappDigital\Cloud\Helper\Data;
use MappDigital\Cloud\Helper\DataLayer as DataLayerHelper;
use MappDigital\Cloud\Model\DataLayer;

class TIDatalayer extends Template
{

    /**
     * @var Data
     */
    protected $_tiHelper = null;

    /**
     * @var DataLayerHelper
     */
    protected $_dataLayerHelper;

    /**
     * @var DataLayer
     */
    protected $_dataLayerModel = null;


    /**
     * @param Context $context
     * @param Data $tiHelper
     * @param DataLayerHelper $dataLayerHelper
     * @param DataLayer $dataLayer
     * @param array $data
     */
    public function __construct(Context $context, Data $tiHelper, DataLayerHelper $dataLayerHelper, DataLayer $dataLayer, array $data = [])
    {
        $this->_tiHelper = $tiHelper;
        $this->_dataLayerHelper = $dataLayerHelper;
        $this->_dataLayerModel = $dataLayer;

        parent::__construct($context, $data);
    }



    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_tiHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    public function getDataLayer()
    {
        $this->_dataLayerModel->setPageDataLayer();
        $data = $this->_dataLayerHelper->mappifyPage($this->_dataLayerModel->getVariables());
        $data = $this->_tiHelper->removeParameterByBlacklist($data);
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
