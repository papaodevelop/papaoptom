<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 19:30
 */

namespace Dowell\NewProducts\Block;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Dowell\NewProducts\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $filterList,
            $visibilityFlag);
    }
}

