<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 19:32
 */
namespace Dowell\NewProducts\Model\Layer;

class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Dowell\NewProducts\Model\Layer $layer,
        array $layersPool
    ) {
        $this->layer = $layer;
        parent::__construct($objectManager, $layersPool);
    }

    public function create($layerType)
    {

    }

    public function loadFromParent(CatalogResolver $parentObj)
    {
        $objValues = get_object_vars($parentObj);
        foreach ($objValues as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * Get layer without saving it, so following Resolver->create will not end up with exception.
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function get()
    {
        $prevLayer = $this->layer;
        $layer = parent::get();
        $this->layer = $prevLayer;
        return $layer;
    }
}