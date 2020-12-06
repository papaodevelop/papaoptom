<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Dowell\OrdersSuppliers\Observer;

use Amasty\Base\Model\Serializer;
use Amasty\Orderexport\Model\Profiles;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Amasty\Orderexport\Helper\File;
use Amasty\Orderexport\Helper\Uploader;
use Amasty\Orderexport\Helper\Filter;

class Export extends \Amasty\Orderexport\Helper\Export
{


    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;
    protected $imageHelperFactory;
    protected $productRepositoryFactory;
    protected $assetRepo;
    protected $request;

    protected $currencyFactory;

    private $_helperFile;
    protected $_logo;

    /**
     * Export constructor.
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $messageManager
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @param LayoutFactory $layoutFactory
     * @param Configurable $catalogProductTypeConfigurable
     * @param ActionFlag $actionFlag
     * @param Product $product
     * @param Context $context
     * @param Page $cmsPage
     * @param Order $modelOrder
     * @param Filesystem $filesystem
     * @param Invoice $modelOrderInvoice
     * @param Config $modelOrderConfig
     * @param Block $cmsBlock
     * @param File $_helperFile
     * @param Uploader $helperUploader
     * @param Filter $helperFilter
     * @param Serializer $serializer
     * @throws FileSystemException
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        ObjectManagerInterface $objectManager,
        ManagerInterface $messageManager,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        LayoutFactory $layoutFactory,
        Configurable $catalogProductTypeConfigurable,
        ActionFlag $actionFlag,
        Product $product,
        Context $context,
        Page $cmsPage,
        Order $modelOrder,
        Filesystem $filesystem,
        Invoice $modelOrderInvoice,
        Config $modelOrderConfig,
        Block $cmsBlock,
        File $helperFile,
        Uploader $helperUploader,
        Filter $helperFilter,
        Serializer $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        $this->currencyFactory = $currencyFactory;
        $this->_logo = $logo;
        $this->_helperFile = $helperFile;
        parent::__construct($registry, $resultPageFactory, $objectManager,
            $messageManager, $localeDate, $storeManager, $layoutFactory, $catalogProductTypeConfigurable,
            $actionFlag, $product, $context, $cmsPage, $modelOrder, $filesystem, $modelOrderInvoice, $modelOrderConfig, $cmsBlock, $helperFile,
            $helperUploader, $helperFilter, $serializer);
        $this->_directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->imageHelperFactory = $imageHelperFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;


        $this->assetRepo = $assetRepository;
        $this->request = $request;

    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->_logo->getLogoSrc();
    }


    protected function getForsageImg()
    {
        $params = array('_secure' => $this->request->isSecure());
        $img = $this->assetRepo->getUrlWithParams('Dowell_OrdersSuppliers::images/forsage_logo.jpg', $params);
        return $img;
    }

    public function getLogoJpgSrc()
    {
        $params = array('_secure' => $this->request->isSecure());
        $img = $this->assetRepo->getUrlWithParams('Dowell_OrdersSuppliers::images/papaoptom_logo.jpg', $params);
        return $img;
    }


    /**
     * @param \Amasty\Orderexport\Model\Profiles $profile
     *
     * @return bool
     */
    public function openFileForWriteXls(&$profile)
    {
        $profile->setData('path', rtrim($profile->getData('path'), '/'));

        $date = $profile->getData('post_date_format') ? date($profile->getData('post_date_format')) : date('_d_m_Y_H_i_s');

        // add Date to folder (==2) or to filename (==1)
        if ($profile->getData('export_add_timestamp') == 2) {
            $profile->setData('path', $profile->getData('path') . '/' . $date);
        }
        if ($profile->getData('export_add_timestamp') == 1) {
            $profile->setData('filename', $profile->getData('filename') . $date);
        }

        // define full file path
        $filePath   = $profile->getData('path') .'/' . $profile->getData('filename') . '.xls';// . ($profile->getData('format') ? 'xml' : 'csv');
        $exportFile = '/'.trim($this->_directory->getAbsolutePath(), '/') . '/' . $filePath;

        // make directory for export if it doesn't exist
        $dirname = dirname($exportFile);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        // save handler for opened file
        $this->fileHandler = fopen($exportFile, 'w+');
        $this->filePath    = $exportFile;

        // save data for opened file in Profile
        $profile->setData('file_path', $filePath);
        $profile->setData('file_path_full', $exportFile);

        return (bool)$this->fileHandler;
    }


    /**
     * @param $ordersDataSelection
     * @param $profile
     * @return bool
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Json_Exception
     */
    // phpcs:disable Generic.Metrics.NestingLevel.TooHigh
    public function exportOrders($ordersDataSelection, &$profile)
    {
        $lastOrderId = 0;
        $lastInvoiceId = 0;
        $this->profile = $profile;
        $exportType = $this->profile->getData('format');
        $fileHead = $this->profile->getData('export_include_fieldnames');
        $fileHeadCont = false;
        $mapping = $this->getMapping($profile);
        $splitOrderItems = $profile->getSplitOrderItems();
        $splitOrderItemsDelim = $profile->getSplitOrderItemsDelim();
        $xmlMainTag = $profile->getXmlMainTag();
        $xmlOrderTag = $profile->getXmlOrderTag();
        $xmlOrderItemTag = $profile->getXmlOrderItemTag();
        $xmlOrderItemsTag = $profile->getXmlOrderItemsTag();
        $staticFieldsBefore = [];
        $staticFieldsAfter = [];

        /** Collect before and after Static fields */
        if ($staticFields = $profile->getStaticFields()) {
            $staticFields = \Zend_Json::decode($staticFields);
            foreach ($staticFields['label'] as $i => $label) {
                if (!empty($label)) {
                    if (!empty($staticFields['position'][$i])) {
                        $staticFieldsAfter[$label] = $staticFields['value'][$i];
                    } else {
                        $staticFieldsBefore[$label] = $staticFields['value'][$i];
                    }
                }
            }
        }

        if ($profile->getData('order_suppliers') == 1) {


            /**@var Order $order */
            // run through all orders and export them
            $pagesTotalNum = $ordersDataSelection->getLastPageNumber();
            $currentPage = 1;
            $orderSuppliers = [];
            do {
                // load collection page by page
                $ordersDataSelection->setCurPage($currentPage);
                $ordersDataSelection->load();
                $ordersItemsData = $this->helperFilter->getItemsForOrders(
                    $profile,
                    $ordersDataSelection
                );
                $ordersItems = $this->helperFilter->getOrdersCollection($ordersDataSelection)
                    ->getItems();


                foreach ($ordersDataSelection->getData() as $orderData) {
                    if (array_key_exists($orderData['entity_id_track'], $ordersItems)) {
                        /** @var Order $order */
                        $order = $ordersItems[$orderData['entity_id_track']];
                        $rows = $this->getRows($splitOrderItems, $orderData, $ordersItemsData, $mapping);

                        /*[Поставщик] => Эльффей
                        [Адрес поставщика] => Розовая 3421
                    [Телефон поставщика] => +38 (093) 657-39-63
                    [Почта поставщика] => katerinamakeeva88@gmail.com
*/


//                        echo '<pre>$rows';
//                        print_r($ordersItems);
//                        echo '</pre>';

                        foreach ($rows as $row) {
                            if (isset($row['Поставщик'])) {
                                $supplierName = $row['Поставщик'];
                                if ($supplierName != '') {
                                    if (!isset($orderSuppliers[$supplierName]))
                                        $orderSuppliers[$supplierName] = [];

                                    $orderSuppliers[$supplierName][] = $row;

                                }
                            }
                        }


                        /*foreach ($rows as $fields) {
                            // XML format
                            if (!empty($staticFieldsBefore)) {
                                $fields = $staticFieldsBefore + $fields;
                            }

                            if (!empty($staticFieldsAfter)) {
                                $fields = $fields + $staticFieldsAfter;
                            }

                            if ($exportType === self::TYPE_XML) {
                                // output line
                                $this->_helperFile->writeXML($fields, $xmlOrderTag, $xmlOrderItemsTag, $xmlOrderItemTag);
                            } elseif ($exportType === self::TYPE_MS_XML) {
                                // prepare XML header if needed & first time output
                                if ($fileHead && !$fileHeadCont) {
                                    $fileHeadCont = $this->_helperFile->writeMsXML(array_keys($fields));
                                }

                                // output line
                                $this->_helperFile->writeMsXML(array_values($fields), $splitOrderItemsDelim);
                            } else { //CSV format
                                // prepare CSV header if needed & first time output
                                if ($fileHead && !$fileHeadCont) {
                                    $fileHeadCont = $this->_helperFile->writeCSV(
                                        array_keys($fields),
                                        $this->profile->getData('csv_delim'),
                                        $this->profile->getData('csv_enclose')
                                    );
                                }

                                // output line
                                $this->_helperFile->writeCSV(
                                    array_values($fields),
                                    $this->profile->getData('csv_delim'),
                                    $this->profile->getData('csv_enclose'),
                                    $splitOrderItemsDelim
                                );
                            }
                        }*/
                        // change order status
                        if ($this->profile->getData('post_status')) {
                            $history = $order->addStatusHistoryComment(
                                __('Amasty Orderexport processing'),
                                $this->profile->getData('post_status')
                            );
                            $history->setIsVisibleOnFront(1);
                            $history->setIsCustomerNotified(0);
                            $history->save();

                            if (!$this->registry->registry('amorderexport_auto_run_triggered')) {
                                $this->registry->register('amorderexport_auto_run_triggered', true);
                            }

                            $order->save();
                        };

                        // save max order id and invoice id
                        if ($lastOrderId < $order->getId()) {
                            $lastOrderId = $order->getId();
                            $lastOrderIncrementId = $order->getIncrementId();
                        }

                        if ($lastInvoiceId < $order->getInvoiceCollection()->getLastItem()->getId()) {
                            $lastInvoiceId = $order->getInvoiceCollection()->getLastItem()->getId();
                            $lastInvoiceIncrementId = $order->getInvoiceCollection()->getLastItem()->getIncrementId();
                        }
                    }
                }


                // increment vars
                $currentPage++;
                //clear collection and free memory
                $ordersDataSelection->clear();
            } while ($currentPage <= $pagesTotalNum);

//            echo '<pre>$rows';
//            print_r($orderSuppliers);
//            echo '</pre>';
//            die();

            $this->openFileForWriteXls($profile);

            $Spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            //$Spreadsheet->getActiveSheet()->setTitle('Заказы поставщикам');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            //$Spreadsheet->setActiveSheetIndex(0);

            //$worksheet = $Spreadsheet->getActiveSheet();


            $thinStyle = array(
                'font' => array(
                    'bold' => false,
                    'color' => array('rgb' => '000000'),
                    'size' => 11,
                    'name' => 'Calibri'
                ));

            $boldItalicStyle = array(
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => '000000'),
                    'size' => 11,
                    'italic' => true,
                    'name' => 'Calibri'
                ));

            $boldStyle = array(
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => '000000'),
                    'size' => 11,
                    'italic' => false,
                    'name' => 'Calibri'
                ));

            $border_thin = array(
                'borders' => array(
                    'inside' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        //BORDER_THIN
                        'color' => array('argb' => '00000000'),
                    )
                )
            );

            $_border_thin = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        //BORDER_THIN
                        'color' => array('argb' => '00000000'),
                    )
                )
            );


            /* $border_thin = array(
                 'borders' => array(
                     'outline' => array(
                         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                         'color' => array('argb' => '00000000'),
                     ),
                 ),
             );
 */
            $border_bold = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '00000000'),
                    ),
                ),
            );


            $logo = $this->getLogoJpgSrc();
            // $logo = 'http://45.77.243.159/pub/static/version1607227411/adminhtml/Magento/backend/en_US/Dowell_OrdersSuppliers/images/papaoptom_logo.jpg';

              // var_dump($logo); die;

            $sheet_num = 1;
            $first_product_row = 6;
            $last_column_word = 'I';
            foreach ($orderSuppliers as $supplierName => $products) {

                $Spreadsheet->createSheet($sheet_num);
                $Spreadsheet->setActiveSheetIndex($sheet_num);
                $worksheet = $Spreadsheet->getActiveSheet();

                $forsageImg = $this->getForsageImg();

                // var_dump($forsageImg);

// http://45.77.243.159/pub/media/logo/websites/1/papaoptom_logo_small.png
                $gdImage = imagecreatefromjpeg($forsageImg);

                $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
                $objDrawing->setName('Logo ');
                $objDrawing->setDescription('Logo');
                $objDrawing->setImageResource($gdImage);
                $objDrawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG);
                $objDrawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG);

                $objDrawing->setResizeProportional(true);
                $objDrawing->setWidth(205);
                $objDrawing->setHeight(90);
                $objDrawing->setOffsetX(5);
                $objDrawing->setOffsetY(5);
                $objDrawing->setCoordinates('A1');
                $objDrawing->setWorksheet($worksheet);


                $gdImage = imagecreatefromjpeg($logo);
                $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
                $objDrawing->setName('Logo ');
                $objDrawing->setDescription('Logo');
                $objDrawing->setImageResource($gdImage);
                $objDrawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG);
                $objDrawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);

                $objDrawing->setResizeProportional(true);
                $objDrawing->setWidth(205);
                $objDrawing->setHeight(90);
                $objDrawing->setOffsetX(5);
                $objDrawing->setOffsetY(5);
                $objDrawing->setCoordinates('F1');
                $objDrawing->setWorksheet($worksheet);


                // Set title
                $worksheet->setTitle($supplierName);

                $worksheet->SetCellValue('A' . $first_product_row, '№');
                $worksheet->SetCellValue('B' . $first_product_row, 'Бренд');
                $worksheet->SetCellValue('C' . $first_product_row, 'Р-ры');
                $worksheet->SetCellValue('D' . $first_product_row, 'Артикул');
                $worksheet->SetCellValue('E' . $first_product_row, 'Ростовок');
                $worksheet->SetCellValue('F' . $first_product_row, 'Кол-во, шт');
                $worksheet->SetCellValue('G' . $first_product_row, 'Цена');
                $worksheet->SetCellValue('H' . $first_product_row, 'Сумма');
                $worksheet->SetCellValue('I' . $first_product_row, 'Картинка');

                $number = rand(5, 100000);
                $date = date('d-m-Y');


                $worksheet->mergeCells('A1:I1');
                //$worksheet->mergeCells('E1:J1');


                $worksheet->SetCellValue('A3', 'Заказ поставщику № ' . $number . ' от ' . $date);
                $worksheet->mergeCells('A3:H3');


                $worksheet->SetCellValue('I2', 'Ул: Базавая 11');
                $worksheet->SetCellValue('I3', 'Viber: +380637145555');
                //$worksheet->mergeCells('A3:I3');





//                $worksheet->setCellValueByColumnAndRow(1, $last_product_row + 4,  'Поставщик: ' . $supplierName.$sup);
//                $worksheet->mergeCellsByColumnAndRow(1, $last_product_row + 4,9,$last_product_row + 4);
//                $worksheet->getRowDimension($last_product_row + 4)->setRowHeight(20);

                $sup ='';
                if (isset($product['address_supplier'])) $sup = ', '.$products[0]['address_supplier'];
                if (isset($product['telephone_supplier'])) $sup .= ', ' . $products[0]['telephone_supplier'];
                if (isset($product['email_supplier'])) $sup .= ', ' . $products[0]['email_supplier'];

                $worksheet->SetCellValue('A4', 'Поставщик: ' . $supplierName.$sup);
                $worksheet->mergeCells('A4:I4');
                $worksheet->getRowDimension(5)->setRowHeight(20);

                $styleArray = array(
                    'borders' => array(
                        'outline' => array(
                            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => array('argb' => '00000000'),
                        ),
                    ),
                );

                $border_thin = array(
                    'borders' => array(
                        'inside' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            //BORDER_THIN
                            'color' => array('argb' => '00000000'),
                        )
                    )
                );

                $worksheet->getStyle('A4:D4')->getAlignment()->setWrapText(true);
                //$worksheet->getStyle('A4:D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                //$worksheet->getStyle('F4:I4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $worksheet->getStyle('A4:D4')->applyFromArray($thinStyle);
               // $worksheet->getStyle('A4:D4')->applyFromArray($_border_thin);
                //->applyFromArray($border_bold);

                //$worksheet->getStyle('A5:D4')->applyFromArray($_border_thin);




                $worksheet->getColumnDimension('F')->setWidth(10);

                $worksheet->getColumnDimension('I')->setWidth(20);

                $worksheet->getRowDimension(1)->setRowHeight(80);
                $worksheet->getRowDimension(4)->setRowHeight(22);
                $worksheet->getRowDimension(3)->setRowHeight(22);
                $worksheet->getRowDimension(2)->setRowHeight(22);


                //$worksheet->getStyle('A7:'.$last_column_word.($last_product_row+2))->applyFromArray($border_thin);
                $worksheet->getStyle('A6:I6')->getAlignment()->setWrapText(true);


                foreach ($products as $i => $product) {

                    $price = $product['price'];
                    $currency=$product['valuta'];
                    if ($currency == 'доллар') {
                        $currencyFrom = 'USD';
                        $currencyTo = 'UAH';
                        $_rate = $this->currencyFactory->create()->load((string)$currencyTo);
                        $rate = $_rate->getRate((string)$currencyFrom);
                        $price = (float)$price * $rate;
                    }



                    $row = $i + $first_product_row + 1;

                    if (!empty($product['par_in_box']))
                        $par_in_box = $product['par_in_box'];
                    else $par_in_box = 1;


                    $_qty = (int)$product['qty'] * $par_in_box;

                    $subtotal = $price * $_qty;

                    $_i=$i+1;
                    $worksheet->SetCellValue('A' . $row, $_i);
                    $worksheet->getStyle('A' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

//                    ->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
//                        'wrap' => true));
                    //->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $worksheet->SetCellValue('C' . $row, $product['sizes']);
                    $worksheet->SetCellValue('D' . $row, $product['sku']);
                    $worksheet->SetCellValue('E' . $row, $product['qty']);
                    $worksheet->SetCellValue('F' . $row, $_qty);
                    $worksheet->SetCellValue('G' . $row, $price);
                    $worksheet->SetCellValue('H' . $row, $subtotal);

                    $sku = $product['sku'];

                    $productId = $product['product_id'];

                    try {
                        $_product = $this->productRepositoryFactory->create()->getById($productId);
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }

                    $brand=$_product->getAttributeText('brand');

                    $worksheet->SetCellValue('B' . $row, $brand);//$_product->getBrand());//$product['brand']);
                    $top_offset = 15;

                    $top_offset = $b_width = 15;
                    $img_width = $img_height = 70;


                    if ($_product) {
                        $imageUrl = $this->imageHelperFactory->create()
                            ->init($_product, 'product_thumbnail_image')->getUrl();

                        $gdImage = imagecreatefromjpeg($imageUrl);

                        $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
                        $objDrawing->setName('Image');
                        $objDrawing->setDescription('Image');
                        $objDrawing->setImageResource($gdImage);
                        $objDrawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG);
                        $objDrawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);

                        $objDrawing->setResizeProportional(true);
                        $objDrawing->setWidth($img_width);
                        $objDrawing->setHeight($img_height);
                        $objDrawing->setOffsetX($top_offset);
                        $objDrawing->setOffsetY(5);
                        $objDrawing->setCoordinates('I' . $row);
                        $objDrawing->setWorksheet($worksheet);

                    }

                    $row_height = 70;
                    $worksheet->getStyle("I" . $row)->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrap' => true));

                    $worksheet->getRowDimension($row)->setRowHeight($row_height);


                }

                $last_product_row = $worksheet->getHighestRow();
                $last_product_column = $worksheet->getHighestColumn();


                $worksheet->getStyle('A' . $first_product_row . ':' . $last_column_word . ($last_product_row + 2))->applyFromArray($border_bold);
                $worksheet->getStyle('A' . $first_product_row . ':' . $last_column_word . ($last_product_row + 2))->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $worksheet->getStyle('A' . $first_product_row . ':D' . ($last_product_row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $worksheet->getStyle('D' . $first_product_row . ':' . $last_column_word . ($last_product_row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $worksheet->getStyle('C' . $first_product_row . ':' . $last_column_word . ($last_product_row + 2))->getAlignment()->setWrapText(true);
                $worksheet->getStyle('A' . $first_product_row . ':' . $last_column_word . ($last_product_row + 2))->applyFromArray($border_thin);


                // Footer
                $worksheet->setCellValueByColumnAndRow(4, $last_product_row + 2, 'Итого:');
                $worksheet->setCellValueByColumnAndRow(5, $last_product_row + 2, '=SUM(E' . ($first_product_row + 1) . ':E' . $last_product_row . ')');
                $worksheet->setCellValueByColumnAndRow(6, $last_product_row + 2, '=SUM(F' . ($first_product_row + 1) . ':F' . $last_product_row . ')');
                $worksheet->setCellValueByColumnAndRow(8, $last_product_row + 2, '=SUM(H' . ($first_product_row + 1) . ':H' . $last_product_row . ')');



//                $worksheet->getStyle('F4:I4')->applyFromArray($thinStyle);
//                $worksheet->getStyle('F4:I4')->applyFromArray($border_bold);

                //$worksheet->getStyleByColumnAndRow(1, $last_product_row + 4,9,$last_product_row + 4)->applyFromArray($thinStyle);
                //$worksheet->getStyleByColumnAndRow(1, $last_product_row + 4,9,$last_product_row + 4)->applyFromArray($border_bold);

                $sheet_num++;
            }

            //$filePath = $profile->getData('path') . '/' . $profile->getData('filename') . time() . '.xls';
            $exportFile = $this->profile->getData('file_path_full');//'/' . trim($this->_directory->getAbsolutePath(), '/') . '/' . $filePath;
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($Spreadsheet, 'Xls');
            $writer->save($exportFile);

        } else {

            // initiate file for export
            if (!$this->_helperFile->openFileForWrite($this->profile)) {
                $this->messageManager->addErrorMessage(
                    __('We could not create file for export. Please, check file path and folder rights for write.')
                );

                return false;
            }

            if ($exportType === self::TYPE_XML) {
                $this->_helperFile->openXmlFile($xmlMainTag);
            } elseif ($exportType === self::TYPE_MS_XML) {
                $this->_helperFile->openMsXmlFile();
            }

            /**@var Order $order */
            // run through all orders and export them
            $pagesTotalNum = $ordersDataSelection->getLastPageNumber();
            $currentPage = 1;

            do {
                // load collection page by page
                $ordersDataSelection->setCurPage($currentPage);
                $ordersDataSelection->load();
                $ordersItemsData = $this->helperFilter->getItemsForOrders(
                    $profile,
                    $ordersDataSelection
                );
                $ordersItems = $this->helperFilter->getOrdersCollection($ordersDataSelection)
                    ->getItems();

                foreach ($ordersDataSelection->getData() as $orderData) {
                    if (array_key_exists($orderData['entity_id_track'], $ordersItems)) {
                        /** @var Order $order */
                        $order = $ordersItems[$orderData['entity_id_track']];
                        $rows = $this->getRows($splitOrderItems, $orderData, $ordersItemsData, $mapping);

                        foreach ($rows as $fields) {
                            // XML format
                            if (!empty($staticFieldsBefore)) {
                                $fields = $staticFieldsBefore + $fields;
                            }

                            if (!empty($staticFieldsAfter)) {
                                $fields = $fields + $staticFieldsAfter;
                            }

                            if ($exportType === self::TYPE_XML) {
                                // output line
                                $this->_helperFile->writeXML($fields, $xmlOrderTag, $xmlOrderItemsTag, $xmlOrderItemTag);
                            } elseif ($exportType === self::TYPE_MS_XML) {
                                // prepare XML header if needed & first time output
                                if ($fileHead && !$fileHeadCont) {
                                    $fileHeadCont = $this->_helperFile->writeMsXML(array_keys($fields));
                                }

                                // output line
                                $this->_helperFile->writeMsXML(array_values($fields), $splitOrderItemsDelim);
                            } else { //CSV format
                                // prepare CSV header if needed & first time output
                                if ($fileHead && !$fileHeadCont) {
                                    $fileHeadCont = $this->_helperFile->writeCSV(
                                        array_keys($fields),
                                        $this->profile->getData('csv_delim'),
                                        $this->profile->getData('csv_enclose')
                                    );
                                }

                                // output line
                                $this->_helperFile->writeCSV(
                                    array_values($fields),
                                    $this->profile->getData('csv_delim'),
                                    $this->profile->getData('csv_enclose'),
                                    $splitOrderItemsDelim
                                );
                            }
                        }
                        // change order status
                        if ($this->profile->getData('post_status')) {
                            $history = $order->addStatusHistoryComment(
                                __('Amasty Orderexport processing'),
                                $this->profile->getData('post_status')
                            );
                            $history->setIsVisibleOnFront(1);
                            $history->setIsCustomerNotified(0);
                            $history->save();

                            if (!$this->registry->registry('amorderexport_auto_run_triggered')) {
                                $this->registry->register('amorderexport_auto_run_triggered', true);
                            }

                            $order->save();
                        };

                        // save max order id and invoice id
                        if ($lastOrderId < $order->getId()) {
                            $lastOrderId = $order->getId();
                            $lastOrderIncrementId = $order->getIncrementId();
                        }

                        if ($lastInvoiceId < $order->getInvoiceCollection()->getLastItem()->getId()) {
                            $lastInvoiceId = $order->getInvoiceCollection()->getLastItem()->getId();
                            $lastInvoiceIncrementId = $order->getInvoiceCollection()->getLastItem()->getIncrementId();
                        }
                    }
                }

                // increment vars
                $currentPage++;
                //clear collection and free memory
                $ordersDataSelection->clear();
            } while ($currentPage <= $pagesTotalNum);
        }

        // save stats after run
        $this->profile->setData('run_records', $ordersDataSelection->count());
        $this->profile->unsetData('filename');

        if ($this->profile->getData('increment_auto') && $lastOrderId) {

            $this->profile->setData('filter_number_from', $lastOrderIncrementId);
            $this->profile->setData('filter_number_to', '');
        }

        if ($this->profile->getData('invoice_increment_auto') && $lastInvoiceId) {
            $this->profile->setData('filter_invoice_from', $lastInvoiceIncrementId);
            $this->profile->setData('filter_invoice_to', '');
        }

        if ($profile->getData('order_suppliers') == 0) {
            // close all opened tags in XML file
            if ($exportType === self::TYPE_XML) {
                $this->_helperFile->closeXmlFile($xmlMainTag);
            } elseif ($exportType === self::TYPE_MS_XML) {
                $this->_helperFile->closeMsXmlFile();
            }
        }


        // archive file
//        echo $this->profile->getData('file_path_full');
//        die();
        $this->attachmentFilePath = $this->profile->getData('file_path_full') . '.zip';
        $this->_helperFile->zip($this->profile->getData('file_path_full'), $this->attachmentFilePath);

        // send via Email if needed
        if ($this->profile->getData('email_use')) {
            $this->sendEmail();
        }

        // send via FTP if needed
        if ($this->profile->getData('ftp_use')) {
            $this->helperUploader->uploadFile($this->profile, $this->profile->getData('file_path_full'));
        }

        // save inner var for outer use
        $this->profile->save();
        $profile = $this->profile;

        return true;
    }

}
