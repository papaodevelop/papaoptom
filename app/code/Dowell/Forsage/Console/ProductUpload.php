<?php
namespace Dowell\Forsage\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductUpload extends Command
{

    const BEGIN = 'begin';

    const END = 'end';

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
    protected $state;

    public function __construct(
//        \Magento\Framework\Filesystem $filesystem,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
//        \Magento\Framework\Event\ManagerInterface $eventManager,
        //\Magento\Framework\App\State $state,
        //\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Dowell\Forsage\Helper\Data $helperForsage,
        \Magento\Framework\App\State $state,
        $name = null
    )
    {


        $this->helperForsage=$helperForsage;

        /*$this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;

        $this->_tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $this->customerRepositoryInterface = $customerRepositoryInterface;

        $this->scopeConfig = $scopeConfig;

        $this->_eventManager = $eventManager;

        $this->_productRepositoryInterface = $productRepositoryInterface;
        */$this->state = $state;

        parent::__construct($name);
    }

    protected
    function configure()
    {
        $options = [
            new InputOption(
                self::BEGIN,
                null,
                InputOption::VALUE_REQUIRED,
                'Begin'
            ),
            new InputOption(
                self::END,
                null,
                InputOption::VALUE_REQUIRED,
                'End'
            )
        ];


        $this->setName('product:upload')
            ->setDescription('upload product from magento')
            ->setDefinition($options);
        parent::configure();

    }

    protected
    function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Start upload products #");

        if (ob_get_level() == 0) ob_start();

        ob_implicit_flush(1);
        echo str_pad('', 1024);
        @ob_flush();
        flush();
        $connection = $this->helperForsage->getConnection();

        //$period='-'.$input->getOption(self::BEGIN).' days';//'-1 year';//210 170 160
        //$period_finish='-'.$input->getOption(self::END).' days';//year'; //120 //80 80
        // -190

        $stock=1;
        //$products=$connection->getProducts($period,$period_finish, $stock);


        //$products=$connection->getChanges('full','-12 hours');
        $products=$connection->getProducts('-12 hours',false,true);
        //$period,$period_finish, $stock);

        //$suppliers=$connection->getSuppliers();//$period,$period_finish, $stock);

        //$this->helperForsage->disableNophoto();

        //$this->helperForsage->getProductsWithoutPhoto();
        foreach ($products->products as $product) {
            $product_id=$product->id;
            $product_request = $connection->GetProduct($product_id);
            if ($product_request->success) {
                $productForsage = $product_request->product;

                $output->writeln("sku " . $productForsage->id . " upload ");

                $this->helperForsage->processForsageData($productForsage);

                @ob_flush();
                flush();
            }
        }

//        echo '<pre>$products';
//        print_r($products);
//        echo '</pre>';
        die();

        if (isset($products->product_ids)) {
            foreach ($products->product_ids as $product_id) {
                $product_request = $connection->GetProduct($product_id);
                if ($product_request->success) {
                    $productForsage = $product_request->product;
                    // $product;//
//                    if (((int)$productForsage->category->id != 11)) {
//                        $output->writeln("ПРопускаем необувь " . $productForsage->category->id . ' ' . $productForsage->category->name);
//
//                        continue;
//                    }
                    //if (((int)$productForsage->quantity > 0) && ((string)$productForsage->category->name == 'Обувь')) {
                        $output->writeln("sku " . $productForsage->id . " upload ");

                        $this->helperForsage->processForsageData($productForsage);

                    @ob_flush();
                    flush();
                }
            }
        }


        die();





        //35 11
        // 103742
        foreach ($suppliers->suppliers as $supplier) {
            if (isset($supplier->id)) {
                if ($supplier->id>798) {//} && ($supplier->id<126)) {//42 11 44 95 93 //124
                    $products = $connection->GetProductsBySupplier($supplier->id);
                    if (isset($products->product_ids)) {
                        foreach ($products->product_ids as $product_id) {
                            $product_request = $connection->GetProduct($product_id);
                            if ($product_request->success) {
                                $productForsage = $product_request->product;

                                if (((int)$productForsage->category->id != 11)) {
                                    $this->helperForsage->processForsageData($productForsage);
                                    if (isset($productForsage->category->child))
                                        $output->writeln("необувь " . $productForsage->category->id . ' ' . $productForsage->category->name.' '.$productForsage->category->child->name);
                                    else
                                        $output->writeln("необувь " . $productForsage->category->id . ' ' . $productForsage->category->name);


                                    $output->writeln("Поставщик " . $supplier->id.' '.$supplier->company . " sku = " . $productForsage->id);
                                    continue;
                                }


                                continue;

                                if (((int)$productForsage->quantity > 0) && ((string)$productForsage->category->name == 'Обувь')) {
                                    $output->writeln("sku " . $productForsage->id . " upload ");
                                    $output->writeln("Поставщик " . $supplier->company . " sku = " . $productForsage->id);
                                    //echo "sku ".$productForsage->id." upload \r\n";
                                    $this->helperForsage->processForsageData($productForsage);


                                    //sleep(1);

                                } else {
                                    $output->writeln("ПРопускаем " . $productForsage->category->name . " " . $productForsage->quantity);
                                    continue;
                                }


                                @ob_flush();
                                flush();
//                        echo '<pre>';
//                        print_r($product_request);
//                        echo '</pre>';
//                        die();
                            }
                        }
                    }
                }
            }
        }

        /*foreach ($suppliers->suppliers as $supplier) {
            if (isset($supplier->id)) {
                if (($supplier->id>41) &&($supplier->id<97)) {//42 11 44 95 93
                    $products = $connection->GetProductsBySupplier($supplier->id);
                    if (isset($products->product_ids)) {
                        foreach ($products->product_ids as $product_id) {
                            $product_request = $connection->GetProduct($product_id);
                            if ($product_request->success) {
                                $productForsage = $product_request->product;
                                // $product;//
                                if (((int)$productForsage->category->id != 11)) {
                                    $output->writeln("ПРопускаем необувь " . $productForsage->category->id . ' ' . $productForsage->category->name);

                                    continue;
                                }
                                if (((int)$productForsage->quantity > 0) && ((string)$productForsage->category->name == 'Обувь')) {
                                    $output->writeln("sku " . $productForsage->id . " upload ");
                                    $output->writeln("Поставщик " . $supplier->company . " sku = " . $productForsage->id);
                                    //echo "sku ".$productForsage->id." upload \r\n";
                                    $this->helperForsage->processForsageData($productForsage);


                                    //sleep(1);

                                } else {
                                    $output->writeln("ПРопускаем " . $productForsage->category->name . " " . $productForsage->quantity);
                                    continue;
                                }


                                @ob_flush();
                                flush();
//                        echo '<pre>';
//                        print_r($product_request);
//                        echo '</pre>';
//                        die();
                            }
                        }
                    }
                }
            }
        }*/

        /*if ($products->success) {
            foreach ($products->products as $product) {

                $product_id=$product->id;

                //GetProduct($product_id, $params=['watermark'=>'bottom'])
                //foreach ($products as $product) {
                    $product_request = $connection->GetProduct($product_id);
                    if ($product_request->success) {
                        $productForsage =$product_request->product;
                        // $product;//


                        $this->helperForsage->processForsageData($productForsage);

                        echo 'sku = '.$productForsage->id."\r\n";

                        sleep(1);
                        @ob_flush();
                        flush();
//                        echo '<pre>';
//                        print_r($product_request);
//                        echo '</pre>';
//                        die();
                    }
                //}
            }
        }
*/

        if (ob_get_level() == 0) ob_start();

    }


}
