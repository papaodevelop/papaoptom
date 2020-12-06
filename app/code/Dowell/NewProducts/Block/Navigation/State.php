<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 19:31
 */

namespace Dowell\NewProducts\Block\Navigation;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Dowell\NewProducts\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $data);
    }
}