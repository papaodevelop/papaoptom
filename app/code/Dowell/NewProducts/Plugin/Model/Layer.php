<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 17:00
 */

namespace Dowell\NewProducts\Plugin\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

//use Magento\Catalog\Model\Layer\FilterList;

class Layer
{
    /**
     * Product collections array
     *
     * @var array
     */
    protected $_productCollections = [];

    /**
     * Key which can be used for load/save aggregation data
     *
     * @var string
     */
    protected $_stateKey = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_catalogProduct;

    /**
     * Attribute collection factory
     *
     * @var AttributeCollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * Layer state factory
     *
     * @var \Magento\Catalog\Model\Layer\StateFactory
     */
    protected $_layerStateFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
     */
    protected $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\StateKey
     */
    protected $stateKeyGenerator;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
     */
    protected $collectionFilter;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    protected $_localeDate;

    /**
     * @param Layer\ContextInterface $context
     * @param Layer\StateFactory $layerStateFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = [],
        \Magento\Framework\View\Element\Context $contextLocale

    )
    {
        $this->_localeDate = $contextLocale->getLocaleDate();
        $this->_layerStateFactory = $layerStateFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;

    }


    public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $result)
    {

        //if ($this->registry->registry('current_newProducts')){


        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        //$collection->addAttributeToSelect('*');
        $result->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('news_from_date', 'desc');


        //  echo '$todayStartOfDayDate'.$todayStartOfDayDate;
        //}


        //die();
//            $key=$this->getCurrentCategory()->getId()."_new";
//        else $key=$this->getCurrentCategory()->getId();

//
//        echo '$key '.$key;
//        die();

        //$result->setOrder('is_in_stock ', 'DESC');
        return $result;
    }
}