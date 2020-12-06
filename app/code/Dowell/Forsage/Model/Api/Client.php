<?php

namespace Dowell\Forsage\Model\Api;

use Dowell\Forsage\Model\Api\Forsage;


/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.03.2018
 * Time: 15:16
 */
class Client
{
    protected $_client;
    protected $forsageApi;
    protected $forsageHelper;

    public function __construct(
        \Dowell\Forsage\Helper\DataFactory $forsageHelper,
        \Dowell\Forsage\Model\Api\Forsage $forsageApi
    )
    {

        $this->forsageApi = $forsageApi;
        $this->forsageHelper = $forsageHelper;
    }


    protected function _getKey()
    {
        $key = $this->forsageHelper->create()->getApikey();
        if (!trim($key)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('No API key configured')
            );
        }
        return $key;
    }

    protected function _getClient()
    {
        if (!$this->_client) {
            $this->_client = $this->forsageApi;
            $key = $this->_getKey();
            $this->_client->setKeyForsage($key);
            //setKey($this->_getKey());
//            new Forsage_Api(
//
//            );
        }
        return $this->_client;
    }

    public function getConnection()
    {
        return $this->_getClient();
    }


}