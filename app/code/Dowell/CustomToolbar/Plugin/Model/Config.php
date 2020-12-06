<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 11:25
 */
namespace Dowell\CustomToolbar\Plugin\Model;

use Magento\Store\Model\StoreManagerInterface;

class Config
{
    protected $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;

    }

    /**
     * Adding custom options and changing labels
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $store = $this->_storeManager->getStore();
        //$currencySymbol = $store->getCurrentCurrency()->getCurrencySymbol();

        //Remove specific default sorting options
        unset($options['position']);
        unset($options['name']);
        unset($options['price']);

        //Changing label
        $customOption['created_at'] = __('Новинки');//Sort News

        //New sorting options
        $customOption['price_desc'] = __('От дорогих к дешевым');//Price from High to Low');
        $customOption['price_unit'] = __('От дешевых к дорогим');//Price from Low to High');

        //"Price from High to Low","От дорогих к дешевым"
//"Price from Low to High","От дешевых к дорогим"

        //Merge default sorting options with custom options
        $options = array_merge($customOption, $options);

        return $options;
    }
}