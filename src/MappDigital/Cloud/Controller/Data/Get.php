<?php
/**
 * @author Mapp Digital
 * @copyright Copyright (c) 2021 Mapp Digital US, LLC (https://www.mapp.com)
 * @package MappDigital_Cloud
 */

namespace MappDigital\Cloud\Controller\Data;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use MappDigital\Cloud\Helper\Data;
use MappDigital\Cloud\Helper\DataLayer as DataLayerHelper;
use MappDigital\Cloud\Model\DataLayer;

class Get implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Http
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $_tiHelper;

    /**
     * @var DataLayer
     */
    protected $_dataLayerModel;

    /**
     * @var DataLayerHelper
     */
    protected $_dataLayerHelper;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param Http $request
     * @param Data $tiHelper
     * @param DataLayer $dataLayer
     * @param DataLayerHelper $dataLayerHelper
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Http $request,
        Data $tiHelper,
        DataLayer $dataLayer,
        DataLayerHelper $dataLayerHelper
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_request = $request;
        $this->_tiHelper = $tiHelper;
        $this->_dataLayerModel = $dataLayer;
        $this->_dataLayerHelper = $dataLayerHelper;
    }

    /**
     * JSON tiConfig and dataLayer with non-cachable data, or just productAdd info
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $isAddToCart = isset($params['add']);
        if(!$isAddToCart) {
            if (isset($params['product'])) {
                $this->_dataLayerModel->setProductDataLayer();
            }
            $this->_dataLayerModel->setCustomerDataLayer();
            $this->_dataLayerModel->setOrderDataLayer();
        }
        $this->_dataLayerModel->setCartDataLayer();
        $dataLayer  = $this->_dataLayerHelper->mappify($this->_dataLayerModel->getVariables());
        $dataLayer = $this->_tiHelper->removeParameterByBlacklist($dataLayer);
        $data = [
                "eventName" => $this->_tiHelper->getAddToCartEventName(),
                "dataLayer" => $dataLayer
        ];
        if(!$isAddToCart) {
            $data['config'] = $this->_tiHelper->getTagIntegrationConfig();
        }
        return $this->resultJsonFactory->create()->setData($data);
    }
}
