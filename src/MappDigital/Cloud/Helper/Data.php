<?php
/**
 * @author Mapp Digital
 * @copyright Copyright (c) 2021 Mapp Digital US, LLC (https://www.mapp.com)
 * @package MappDigital_Cloud
 */
namespace MappDigital\Cloud\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /**
     * @var string
     */
    const XML_PATH_ENABLE = 'tagintegration/general/enable';
    /**
     * @var string
     */
    const XML_PATH_TAGINTEGRATION_ID = 'tagintegration/general/tagintegration_id';
    /**
     * @var string
     */
    const XML_PATH_TAGINTEGRATION_DOMAIN = 'tagintegration/general/tagintegration_domain';
    /**
     * @var string
     */
    const XML_PATH_CUSTOM_DOMAIN = 'tagintegration/general/custom_domain';
    /**
     * @var string
     */
    const XML_PATH_CUSTOM_PATH = 'tagintegration/general/custom_path';
    /**
     * @var string
     */
    const XML_PATH_ATTRIBUTE_BLACKLIST = 'tagintegration/general/attribute_blacklist';
    /**
     * @var string
     */
    const XML_PATH_ADD_TO_CART_EVENT_NAME = 'tagintegration/general/add_to_cart_event_name';
    /**
     * @var string
     */
    const XML_PATH_ACQUIRE = 'tagintegration/general/acquire';

    /**
     * @var Repository
     */
    protected $_assetRepository;

    /**
     * @param Context $context
     * @param Repository $assetRepository
     */
    public function __construct(Context $context, Repository $assetRepository)
    {
        $this->_assetRepository = $assetRepository;

        parent::__construct($context);
    }

    /**
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    private function getAttributeBlacklist()
    {
        $attributeBlacklist = $this->scopeConfig->getValue(self::XML_PATH_ATTRIBUTE_BLACKLIST, ScopeInterface::SCOPE_STORE);
        return preg_split("/(?:\r\n|,)/", $attributeBlacklist);
    }

    /**
     * @return string | null
     */
    private function getAcquireLink()
    {
        if(preg_match('/id=(\d+?)&m=(\d+?)\D/', $this->scopeConfig->getValue(self::XML_PATH_ACQUIRE, ScopeInterface::SCOPE_STORE), $ids))
        {
            return 'https://c.flx1.com/' . $ids[2] . '-' . $ids[1] .'.js?id=' . $ids[1] . '&m=' . $ids[2];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getTagIntegrationConfig()
    {
        return [
            'tiId' => $this->scopeConfig->getValue(self::XML_PATH_TAGINTEGRATION_ID, ScopeInterface::SCOPE_STORE),
            'tiDomain' => $this->scopeConfig->getValue(self::XML_PATH_TAGINTEGRATION_DOMAIN, ScopeInterface::SCOPE_STORE),
            'customDomain' => $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_DOMAIN, ScopeInterface::SCOPE_STORE),
            'customPath' => $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_PATH, ScopeInterface::SCOPE_STORE),
            'option' => (object)[],
            'acquire' => $this->getAcquireLink()
        ];
    }

    /**
     * @return string
     */
    public function getAddToCartEventName()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_ADD_TO_CART_EVENT_NAME, ScopeInterface::SCOPE_STORE);
        return is_null($configValue) ? 'add-to-cart': $configValue;
    }

    public function removeParameterByBlacklist(array $data = [])
    {
        $blacklist = $this->getAttributeBlacklist();
        for ($i = 0, $l = count($blacklist); $i < $l; $i++) {
            $key = $blacklist[$i];

            if (strpos($key, '*') !== false) {
                $keyRegExp = implode('.*', explode('*', $key));
                $matches = preg_grep('/' . $keyRegExp . '/', array_keys($data));
                foreach ($matches as $k => $v) {
                    unset($data[$v]);
                }
            } else {
                if (array_key_exists($key, $data)) {
                    unset($data[$key]);
                }
            }
        }

        $data['blacklist'] = $blacklist;
        return $data;
    }
}
