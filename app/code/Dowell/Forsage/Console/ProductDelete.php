<?php

namespace Dowell\Forsage\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductDelete extends Command
{

    /*protected $_tmpDirectory;

    protected $_customerFactory;

    protected $customerRepositoryInterface;


    protected $scopeConfig;

    protected $customer;

    protected $_eventManager;


    protected $_productFactory;

    protected $productMagentoModel;

    protected $_marketplaceHelperData;

    protected $_portCollectionFactory;
*/
    /**
     * @var CategoryLinkManagementInterface
     */
    /*protected $categoryLinkManagement;

    protected $_categoryFactory;
    protected $_productCollectionFactory;
*/
    /** @var \Magento\Framework\App\State * */
    /* private $state;
     protected $client;
     protected $helperCampaign;
     protected $_mpProductCollectionFactory;
     protected $productValidator;
     protected $helperNotification;
 */
    protected $helperForsage;
    protected $collectionProduct;
    protected $productRepository;
    private $state;

    public function __construct(
//        \Magento\Framework\Filesystem $filesystem,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
//        \Magento\Framework\Event\ManagerInterface $eventManager,
        //\Magento\Framework\App\State $state,
        //\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Dowell\Forsage\Helper\Data $helperForsage,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\App\State $state,
        $name = null
    )
    {

        $this->collectionProduct = $productCollectionFactory;
        $this->helperForsage = $helperForsage;

        /*$this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;

        $this->_tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $this->customerRepositoryInterface = $customerRepositoryInterface;

        $this->scopeConfig = $scopeConfig;

        $this->_eventManager = $eventManager;

        $this->_productRepositoryInterface = $productRepositoryInterface;
        */
        $this->productRepository = $productRepositoryInterface;
        $this->state = $state;
        parent::__construct($name);
    }

    protected
    function configure()
    {

        $options = [

        ];

        $this->setName('product:delete')
            ->setDescription('delete product from magento')
            ->setDefinition($options);
        parent::configure();

    }

    protected
    function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Start delete products #");

        if (ob_get_level() == 0) ob_start();

        ob_implicit_flush(1);
        echo str_pad('', 1024);
        @ob_flush();
        flush();
        $products = $this->collectionProduct->create();
//            ->setPageSize(20)
//            ->setCurPage(1);
        foreach ($products as $product) {
            $sku = $product->getSku();
            echo 'sku = '.$sku."\r\n\r\n";
            $this->productRepository->deleteById($sku);
            @ob_flush();
            flush();
        }


    }


}
