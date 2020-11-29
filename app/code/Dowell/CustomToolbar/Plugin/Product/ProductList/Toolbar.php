<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 11:27
 */
namespace Dowell\CustomToolbar\Plugin\Product\ProductList;

class Toolbar
{
    /**
     * Plugin
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed,
        $collection
    ) {
        $currentOrder = $subject->getCurrentOrder();
        $result = $proceed($collection);

        if ($currentOrder) {
            if ($currentOrder == 'created_at') {
                $subject->getCollection()->setOrder('created_at', 'desc');
            }
            elseif ($currentOrder == 'price_desc') {
                $subject->getCollection()->setOrder('price', 'desc');
            } elseif ($currentOrder == 'price_asc') {
                $subject->getCollection()->setOrder('price', 'asc');
            }
         elseif ($currentOrder == 'special_price') {
            $subject->getCollection()->setOrder('special_price', 'desc');
        }
        }

        return $result;
    }
}