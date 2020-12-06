<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 25.08.2020
 * Time: 11:30
 */

namespace Dowell\Forsage\Helper;


use Magento\Framework\App\Helper\AbstractHelper;


use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Framework\Registry;


use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;


use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Data Helper
 *
 * @package Webfresh\ThemeOptions\Helper
 */
class Data extends AbstractHelper
{

    const KEY_FORSAGE = 'forsage/general/api_key';

    const PARAMS_SEX_FORSAGE = 39;


    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    protected $apiForsage;
    protected $categoryRepository;

    protected $currencyFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoriesFactory;
    protected $categoryFactory;
    protected $additionalHelper;
    protected $setup;

    protected $eavSetupFactory;
    protected $_attributeRepository;
    protected $_attributeOptionManagement;
    protected $_option;
    protected $_attributeOptionLabel;
    protected $_attributeRepositoryFactory;
    protected $productFactory;
    protected $productRepository;
    protected $stockRegistry;

    protected $galleryProcessor;
    protected $gallery;
    protected $_tmpDirectory;
    protected $readHandler;
    protected $logger;
    protected $forsageLog;
    protected $productModelFactory;
    protected $collectionProductFactory;


    /**
     * Data constructor.
     *
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupColl
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Dowell\Forsage\Model\Api\Client $apiForsage,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Dowell\Forsage\Helper\AdditionalFactory $AdditionalHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        AttributeRepositoryInterface $attributeRepositoryManagement,
        \Magento\Eav\Api\AttributeRepositoryInterfaceFactory $attributeRepositoryFactory,
        \Magento\Eav\Model\Entity\Attribute\OptionLabelFactory $attributeOptionLabel,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $option,
        \Magento\Eav\Model\Entity\Attribute\OptionManagement $attributeOptionManagement,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Product\Gallery\Processor $GalleryProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $ProductGallery,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $ReadHandler,
        LoggerInterface $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $resourceCollectionProduct
    )
    {


        $this->indexerRegistry = $indexerRegistry;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;//->critical($e->getMessage());
        //TMP
        $this->readHandler = $ReadHandler;
        $this->_tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->galleryProcessor = $GalleryProcessor;
        $this->gallery = $ProductGallery;

        $this->productModelFactory = $productModelFactory;

        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;

        $this->_attributeRepositoryFactory = $attributeRepositoryFactory;

        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;

        $this->_option = $option;
        $this->_attributeOptionLabel = $attributeOptionLabel;


        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->additionalHelper = $AdditionalHelper;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->apiForsage = $apiForsage;
        $this->categoriesFactory = $categoriesFactory;

        $this->collectionProductFactory = $resourceCollectionProduct;
        parent::__construct($context);

    }


    public function getProductsWithoutPhoto()
    {

        $products = $this->collectionProductFactory->create();
        //->getResourceCollection()->addAttributeToSelect('*')
        $products->addAttributeToFilter('status', array('eq' => '1'))
            ->addAttributeToFilter(
                array(
                    array(
                        'attribute' => 'image',
                        'null' => '1'
                    ),
                    array(
                        'attribute' => 'small_image',
                        'null' => '1'
                    ),
                    array(
                        'attribute' => 'thumbnail',
                        'null' => '1'
                    ),
                    array(
                        'attribute' => 'image',
                        'nlike' => '%/%/%'
                    ),
                    array(
                        'attribute' => 'small_image',
                        'nlike' => '%/%/%'
                    ),
                    array(
                        'attribute' => 'thumbnail',
                        'nlike' => '%/%/%'
                    )
                ),
                null,
                'left'
            )
            ->setOrder('created_at', 'DESC');


        foreach ($products as $pro)
            echo count($products) . "\r\n\r\n";
        echo $products->getSelect();
        die();

        return $products;
    }

    public function disableNophoto()
    {
        $products = [103742, 124863, 124866, 124911, 124928, 124930, 124931, 124942, 124943, 124944, 124945, 124957, 125440, 125441, 125442, 125443, 125444, 125445, 125446, 125447, 125448, 125449, 125450, 125451, 125462, 125463, 125465, 125466, 125467, 125468, 125469, 125470, 125471, 125472, 125473, 125518, 125522, 125523, 125524, 125525, 125526, 125528, 125532, 125533, 125534, 125535, 125536, 125537, 125538, 125539, 125540, 125541, 125542, 125543, 125544, 125545, 125574, 125575, 125576, 125577, 125586, 125588, 125589, 125590, 125603, 125604, 125605, 125606, 125607, 125608, 125609, 125610, 125611, 125612, 125613, 125614, 125615, 125616, 125617, 125619, 125620, 125621, 125622, 125623, 125624, 125626, 125627, 125628, 125633, 125952, 125955, 125958, 125962, 125963, 126681, 126682, 126683, 126685, 126687, 126693, 126697, 126698, 126701, 126702, 126703, 126704, 126708, 126709, 126710, 126711, 126712, 126713, 126714, 126716, 126718, 126719, 126725, 126727, 126728, 126729, 126730, 126731, 126732, 126733, 126740, 126794, 126795, 126796, 126797, 126798, 126799, 126800, 126801, 126802, 126803, 126804, 126806, 126807, 126808, 126809, 126810, 126811, 126812, 126813, 126814, 126815, 126816, 126817, 126818, 126819, 126820, 126823, 126824, 126825, 126826, 126827, 126828, 126829, 126830, 126831, 126832, 126834, 126835, 126837, 126840, 126841, 126844, 126845, 126846, 126847, 126848, 126849, 126881, 126882, 126883, 126884, 132123, 132127, 132182, 132194, 132212, 132214, 132215, 132217, 132219, 132246, 132247, 132268, 132269, 132271, 132273, 132274, 132275, 132278, 132281, 132315, 132316, 132318, 132356, 132357, 132358, 132360, 132363, 132377, 132379, 132380, 132381, 132382, 132384, 132398, 132399, 132400, 132402, 132403, 132404, 132406, 132407, 132409, 132427, 132438, 132439, 132440, 132441, 132442, 132450, 132451, 132452, 132453, 132454, 132455, 132456, 132616, 132617, 132618, 132619, 132620, 132624, 132625, 132626, 132629, 132630, 132631, 132681, 132682, 132683, 132685, 132686, 132687, 132689, 132691, 132693, 132694, 132695, 132696, 132697, 132698, 132700, 132701, 132702, 132703, 132705, 132706, 132711, 132712, 132713, 132714, 132715, 132717, 132721, 132724, 132725, 132727, 132730, 132731, 132732, 132733, 132735, 132736, 132737, 132738, 132740, 132741, 132742, 132746, 132747, 132748, 132751, 132752, 132754, 132755, 132758, 132761, 132802, 132803, 132804, 132805, 132806, 132807, 132808, 132809, 132811, 132812, 132813, 132814, 132815, 132816, 132819, 132821, 132824, 132825, 132826, 132838, 132839, 132842, 132843, 132844, 132845, 132846, 132867, 132868, 132870, 132902, 132904, 132905, 132909, 132913, 132916, 132919, 132930, 132931, 132932, 132933, 132934, 132935, 132936, 132937, 132938, 132939, 132940, 132947, 132950, 132953, 132958, 132959, 132960, 132961, 132963, 132964, 132965, 132966, 133012, 133037, 133079, 133080, 133081, 133085, 133088, 133099, 133100, 133102, 133103, 133105, 133106, 133107, 133110, 133111, 133112, 133116, 133117, 133118, 133122, 133123, 133124, 133125, 133127, 133128, 133130, 133131, 133132, 133133, 133134, 133135, 133136, 133137, 133138, 133139, 133140, 133141, 133142, 133143, 133144, 133145, 133155, 133200, 133208, 133212, 133215, 133216, 133217, 133218, 133219, 133220, 133221, 133222, 133223, 133224, 133225, 133226, 133227, 133228, 133229, 133230, 133233, 133234, 133235, 133236, 133237, 133238, 133241, 133242, 133243, 133244, 133245, 133246, 133247, 133248, 133249, 133251, 133252, 133253, 133254, 133255, 133256, 133258, 133259, 133260, 133261, 133263, 133264, 133265, 133266, 133267, 133268, 133269, 133270, 133271, 133272, 133273, 133274, 133275, 133276, 133277, 133278, 133279, 133280, 133281, 133282, 133283, 133284, 133285, 133286, 133287, 133288, 133289, 133290, 133291, 133292, 133293, 133294, 133298, 133299, 133300, 133301, 133302, 133303, 133304, 133305, 133306, 133307, 133308, 133309, 133310, 133311, 133312, 133313, 133314, 133315, 133316, 133317, 133318, 133320, 133321, 133322, 133324, 133325, 133326, 133327, 133328, 133329, 133330, 133331, 133332, 133336, 133337, 133338, 133339, 133340, 133341, 133342, 133343, 133344, 133345, 133346, 133347, 133348, 133349, 133350, 133351, 133352, 133353, 133354, 133355, 133356, 133357, 133358, 133359, 133360, 133361, 133362, 133363, 133364, 133365, 133366, 133368, 133369, 133370, 133371, 133372, 133373, 133374, 133375, 133376, 133377, 133378, 133379, 133380, 133381, 133382, 133383, 133384, 133385, 133386, 133387, 133389, 133390, 133391, 133392, 133393, 133394, 133395, 133396, 133397, 133398, 133399, 133400, 133401, 133402, 133403, 133404, 133405, 133406, 133407, 133408, 133409, 133410, 133411, 133412, 133413, 133414, 133415, 133416, 133417, 133418, 133419, 133420, 133421, 133422, 133423, 133424, 133425, 133426, 133427, 133428, 133429, 133430, 133431, 133432, 133433, 133434, 133435, 133436, 133438, 133439, 133440, 133441, 133442, 133443, 133444, 133445, 133447, 133450, 133451, 133453, 133454, 133455, 133456, 133457, 133458, 133460, 133461, 133464, 133465, 133466, 133467, 133468, 133470, 133471, 133472, 133473, 133475, 133476, 133477, 133478, 133479, 133480, 133481, 133482, 133483, 133484, 133485, 133486, 133487, 133488, 133489, 133491, 133492, 133493, 133494, 133495, 133496, 133498, 133499, 133500, 133501, 133502, 133503, 133504, 133505, 133506, 133509, 133510, 133511, 133512, 133513, 133514, 133515, 133516, 133517, 133518, 133519, 133520, 133521, 133522, 133523, 133524, 133525, 133526, 133527, 133528, 133529, 133530, 133531, 133532, 133533, 133534, 133535, 133536, 133537, 133539, 133540, 133541, 133542, 133543, 133544, 133545, 133546, 133547, 133548, 133549, 133550, 133551, 133552, 133553, 133554, 133555, 133556, 133557, 133558, 133559, 133560, 133561, 133562, 133563, 133564, 133565, 133566, 133567, 133568, 133569, 133570, 133571, 133572, 133573, 133574, 133576, 133588, 133589, 133590, 133591, 133592, 133593, 133594, 133595, 133596, 133597, 133598, 133599, 133600, 133601, 133602, 133603, 133604, 133605, 133606, 133607, 133608, 133610, 133611, 133612, 133613, 133614, 133615, 133616, 133617, 133618, 133619, 133620, 133621, 133622, 133623, 133624, 133625, 133627, 133628, 133629, 133630, 133631, 133632, 133633, 133634, 133635, 133636, 133637, 133638, 133639, 133640, 133641, 133642, 133644, 133645, 133646, 133647, 133648, 133649, 133650, 133651, 133652, 133654, 133656, 133658, 133659, 133660, 133661, 133663, 133664, 133665, 133667, 133668, 133669, 133670, 133671, 133672, 133673, 133674, 133675, 133676, 133677, 133678, 133679, 133680, 133681, 133682, 133683, 133684, 133686, 133687, 133690, 133692, 133693, 133694, 133695, 133696, 133697, 133698, 133699, 133700, 133701, 133702, 133703, 133704, 133705, 133706, 133707, 133708, 133709, 133710, 133712, 133713, 133714, 133715, 133716, 133717, 133718, 133719, 133720, 133722, 133724, 133725, 133726, 133727, 133729, 133731, 133734, 133736, 133737, 133738, 133739, 133740, 133741, 133742, 133743, 133745, 133746, 133747, 133748, 133749, 133750, 133752, 133753, 133754, 133755, 133756, 133757, 133758, 133759, 133760, 133761, 133762, 133763, 133765, 133766, 133767, 133769, 133770, 133771, 133775, 133776, 133777, 133778, 133779, 133780, 133781, 133782, 133783, 133784, 133785, 133786, 133787, 133788, 133789, 133790, 133792, 133798, 133800, 133801, 133802, 133804, 133806, 133807, 133808, 133809, 133810, 133812, 133813, 133814, 133815, 133816, 133817, 133821, 133822, 133824, 133827, 133828, 133829, 133830, 133831, 133832, 133839, 133841, 133842, 133843, 133844, 133845, 133846, 133847, 133848, 133849, 133850, 133851, 133853, 133854, 133855, 133857, 133858, 133860, 133861, 133862, 133863, 133864, 133865, 133866, 133868, 133869, 133870, 133871, 133874, 133876, 133877, 133879, 133880, 133881, 133885, 133886, 133887, 133890, 133893, 133894, 133895, 133896, 133897, 133898, 133901, 133902, 133905, 133906, 133907, 133908, 133909, 133910, 133911, 133912, 133913, 133914, 133915, 133916, 133917, 133918, 133919, 133920, 133921, 133923, 133924, 133925, 133927, 133928, 133929, 133930, 133931, 133932, 133933, 133934, 133935, 133936, 133937, 133938, 133939, 133940, 133941, 133942, 133943, 133945, 133946, 133947, 133948, 133949, 133951, 133952, 133953, 133954, 133955, 133956, 133957, 133958, 133959, 133960, 133961, 133962, 133963, 133964, 133965, 133966, 133967, 133968, 133969, 133970, 133971, 133973, 133974, 133975, 133976, 133977, 133978, 133979, 133980, 133981, 133982, 133983, 133984, 133985, 133986, 133987, 133988, 133989, 133990, 133991, 133992, 133993, 133994, 133995, 133996, 133997, 133998, 133999, 134000, 134001, 134002, 134003, 134004, 134005, 134006, 134007, 134008, 134009, 134010, 134011, 134012, 134013, 134014, 134018, 134020, 134021, 134022, 134023, 134024, 134025, 134030, 134031, 134032, 134033, 134034, 134038, 134041, 134042, 134043, 134044, 134045, 134046, 134047, 134048, 134049, 134050, 134051, 134052, 134053, 134054, 134055, 134056, 134057, 134058, 134059, 134061, 134062, 134063, 134064, 134065, 134066, 134067, 134068, 134069, 134070, 134071, 134072, 134073, 134074, 134075, 134076, 134082, 134084, 134085, 134089, 134090, 134091, 134092, 134093, 134094, 134095, 134096, 134101, 134103, 134107, 134108, 134121, 134127, 134130, 134131, 134132, 134134, 134135, 134136, 134138, 134139, 134140, 134141, 134142, 134143, 134144, 134145, 134146, 134147, 134150, 134153, 134154, 134155, 134157, 134160, 134161, 134163, 134164, 134178, 134179, 134180, 134186, 134187, 134188, 134191, 134192, 134197, 134200, 134201, 134207, 134208, 134209, 134210, 134211, 134212, 134213, 134215, 134216, 134217, 134218, 134222, 134258, 134259, 134260, 134261, 134263, 134264, 134269, 134270, 134272, 134274, 134287, 134298, 134300, 134302, 134306, 134311, 134322, 134328, 134332, 134333, 134334, 134335, 134336, 134337, 134344, 134346, 134358, 134383, 134384, 134385, 134387, 134388, 134390, 134391, 134394, 134395, 134396, 156060, 169450, 207068, 207731, 232195, 250975, 289712, 291453, 298233, 310996, 346338, 346339, 346345, 347288, 348545, 352176, 353979, 355053, 355054, 355057, 355058, 366448, 377921, 378099, 389171, 390534, 408306, 408307, 408308, 408309, 408817, 410593, 412536, 418014, 420645, 422256, 431257, 431258, 431468, 432621, 432625, 432629, 434217, 438053, 438353, 442145, 442953, 443327, 446067, 449694, 450433, 452897, 45953, 460901, 476536, 482763, 494747, 495273, 499579, 505383, 522211, 523114, 523115, 523116, 523117, 526908, 531465, 531902, 532319, 534925];
        foreach ($products as $forsageId) {

            $product = $this->productRepository->create()->get((string)$forsageId);
            if ($product) {
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $this->productRepository->create()->save($product);
                //$this->productRepository->save($product);
            }

        }
    }

    /**
     * @return int
     */
    public function getApikey()
    {
        return $this->scopeConfig->getValue(self::KEY_FORSAGE, ScopeInterface::SCOPE_STORE);
    }


    public function getConnection()
    {
        return $this->apiForsage->getConnection();
    }


    public function searchOptionByAttributeAndOption($optionLabel, $atributeCode, $attribute = false)
    {

        if (!$attribute) {
            try {
                $attribute = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $atributeCode);
            } catch (NoSuchEntityException $e) {
                return false;
            }

        }
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                //echo $optionLabel . ' ' . $option['label'] . "\r\n\r\n";
                if ($option['label'] == $optionLabel)
                    return $option['value'];

            }
        }

        return false;


    }

    protected function addOption($attributeId, $optionLabel)
    {
        $option = $this->_option->create();
        $labelObj = $this->_attributeOptionLabel->create();

        $labelObj->setStoreId(0);
        $labelObj->setLabel($optionLabel);
        $option->setLabel($optionLabel);
        $option->setStoreLabels([$labelObj]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);
        $option->setAttributeId($attributeId)->getResource()->save($option);
        $this->_attributeOptionManagement->add(\Magento\Catalog\Model\Product::ENTITY, $attributeId, $option);
    }

    public function searchAttribute($data)
    {
        $attributeName = $data['name'];
        if (!isset($data['code'])) {
            $code = $this->additionalHelper->create()->translite($attributeName);
        } else $code = $data['code'];

        try {
            $attribute = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $code);//'payment_terms');

        } catch (NoSuchEntityException $e) {
            ///echo '$code = '.$code;
//            die();
            return false;
        }
        return $attribute;

    }


    public function createProductAttribute($data, $options = [])
    {

        $attribute = $this->searchAttribute($data);
        $name = trim($data['name']);
        if (isset($data['type']))
            $type = $data['type'];
        else $type = 'text';

        $code = $this->additionalHelper->create()->translite($name);
        $code = str_replace('-', '_', $code);

        if (!$attribute) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

            if ($type == 'select') {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'varchar',//$type,
                        'backend' => '',
                        'frontend' => '',
                        'label' => $name,
                        'input' => $type,//'text',
                        'class' => '',
                        'source' => '',
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => '',
                        'note' => $name,
                        'option' => $options
                    ]
                );
            } else {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'varchar',//$type,
                        'backend' => '',
                        'frontend' => '',
                        'label' => $name,
                        'input' => $type,//'text',
                        'class' => '',
                        'source' => '',
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => '',
                        'note' => $name
                    ]
                );
            }


            $this->setup->endSetup();
            $eavSetup->cleanCache();

        } elseif ($type == 'select') {

//            echo '<pre>$data';
//            print_r($data);
//            echo '</pre>';

            if (count($options) > 0) {
                $attributeId = $attribute->getAttributeId();

                foreach ($options as $label) {

                    if ($label != '') {
                        $this->addOption($attributeId, $label);
                    }
                }
            }
        }


        try {
            $attribute = $this->_attributeRepositoryFactory->create()->get(\Magento\Catalog\Model\Product::ENTITY, $code);
            return $attribute;

        } catch (NoSuchEntityException $e) {

            if ($type == 'select') {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'varchar',//$type,
                        'backend' => '',
                        'frontend' => '',
                        'label' => $name,
                        'input' => $type,//'text',
                        'class' => '',
                        'source' => '',
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => '',
                        'note' => $name,
                        'option' => $options
                    ]
                );
            } else {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'varchar',//$type,
                        'backend' => '',
                        'frontend' => '',
                        'label' => $name,
                        'input' => $type,//'text',
                        'class' => '',
                        'source' => '',
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => '',
                        'note' => $name
                    ]
                );
            }

            $this->setup->endSetup();
            try {
                $eavSetup->cleanCache();
                $attribute = $this->_attributeRepositoryFactory->create()->get(\Magento\Catalog\Model\Product::ENTITY, $code);
                //$eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
                //$this->_attributeRepositoryFactory->create()->get(\Magento\Catalog\Model\Product::ENTITY, $code);
                return $attribute;

            } catch (NoSuchEntityException $e) {
                echo '$code NoSuchEntityException ' . $code . ' ' . $type . ' ' . $name;
                die();
            }


        }


    }


    protected function processCharacteristics($dataForsage)
    {
        $characteristics = $dataForsage->characteristics;

        $dataAttributes = [];

        foreach ($characteristics as $characteristic) {
            $data = [];
            $name = (string)$characteristic->name;
            //if ($name != 'Дата съемки') {
            $value = (string)$characteristic->value;
            $type = (string)$characteristic->type;
            switch ($type) {
                case 'refbook':
                    $data['type'] = 'select';
                    break;
                case 'link':
                case 'image':
                case 'text':
                    $data['type'] = 'varchar';
                    break;
                case 'number':
                    $data['type'] = 'varchar';
                    break;

            }

            $data['name'] = $name;
            $data['value'] = $value;

            $code = false;
            switch (trim($name)) {
                //Комментарии (доп. описание)
                case 'Цвет':
                    $code = 'color';
                    break;

                case 'Материал изделия':
                    $code = 'material';
                    break;
                case 'Размерная сетка':
                    $code = 'size';
                    break;

                //Повторные размеры
                case 'Пар в ящике':

                    $code = 'par_inbox';
                    break;

                case 'Материал подкладки':

                    $code = 'material_podkladki';
                    break;


                case 'Материал подошвы':
                    $code = 'material_podoshvi';
                    break;

//                    case 'Страна':
//                        $code = 'country_of_manufacture';
//                        break;
                /*Единица измерения
                Высота каблука, см
                Высота платформы
                Размер(на фото)*/
                case 'Цена закупки':
                    $code = 'price_zak';
                    break;
                case 'Цена продажи':
                    $code = 'price_unit';
                    break;


                case 'Сезон':
                    $code = 'sezon';
                    break;

                //Дата съемки
                //Валюта закупки
                case 'Валюта продажи':
                    $code = 'valuta';
                    break;

                case 'Пол':
                    $code = 'pol';
                    break;

//            Тип
//            Старая цена закупки
//            Старая цена продажи
                //Материал стельки
                //Видеообзор
//                case '':
//                    break;
                //default:


            }

            if ($code) {
                $data['code'] = $code;
            }

            if ($value != '') {
                $attribute = $this->searchAttribute($data);
                if (!$attribute) {
                    if ($value != '')
                        $attribute = $this->createProductAttribute($data, [$value]);
                    else
                        $attribute = $this->createProductAttribute($data);

                }


                if ($data['type'] == 'select') {
                    $valueMagento = $this->searchOptionByAttributeAndOption($value, $attribute->getAttributeCode(), $attribute);

                    if (!$valueMagento) {

                        $this->addOption($attribute->getAttributeId(), $value);
                        $valueMagento = $this->searchOptionByAttributeAndOption($value, $attribute->getAttributeCode(), $attribute);

                    }
                } else $valueMagento = $value;


                $dataAttributes[$attribute->getAttributeCode()] = $valueMagento;


                if ($code == 'price_unit') {
                    foreach ($characteristics as $characteristic) {
                        if ($characteristic->id == 35) {
                            $currency = $characteristic->value;
                        }

                    }

                    $par_inbox = 1;
                    foreach ($characteristics as $characteristic) {
                        if ($characteristic->id == 8) {
                            $par_inbox = (int)$characteristic->value;
                        }

                    }
                    if (isset($dataAttributes['price_unit'])) {
                        $price = $dataAttributes['price_unit'];

                        if ($currency == 'доллар') {
                            $currencyFrom = 'USD';
                            $currencyTo = 'UAH';

                            $_rate = $this->currencyFactory->create()->load((string)$currencyTo);
                            $rate = $_rate->getRate((string)$currencyFrom);
                            //echo '$price ' . $price . ' $rate=' . $rate . "\r\n\r\n";
                            $price = (float)$price * $rate;
                            $dataAttributes['price_unit'] = $price;//$par_inbox * $price;
                        }


                    }
                }

            }
//                echo '$valueMagento = ' . $value . ' vvvvv' . $valueMagento . ' ' . $attribute->getAttributeCode() . ' ' . $attribute->getAttributeId() . "\r\n\r\n";
//                die();
            //}
            //}

        }


        return $dataAttributes;
    }


    public function getPathCategory($forsageData)
    {
        $mainCategoryForsageId = $forsageData->category->id;
        $mainCategoryForsageName = $forsageData->category->name;
        $parentIdRoot = 2;
        $level = 2;// 1
        $path = [];
        $path[] = 1;
        $path[] = 2;

        $sexForsage = $this->getSexProduct($forsageData);
        $categoryFirst = $this->getCategoryByLevel($level, $mainCategoryForsageId);
        if (!empty($categoryFirst->getId())) {
            $path[] = $categoryFirst->getId();

        } else {
            $data = [];
            $data['name'] = $mainCategoryForsageName;
            $data['parent_id'] = $parentIdRoot;
            $data['forsage_id'] = $mainCategoryForsageId;
            $data['path'] = $path;
//            echo '<pre>$data'. $mainCategoryForsageId.' '.$level;
//            print_r($data);
//            echo '</pre>';


            $categoryFirst = $this->createCategory($data);
            if ($categoryFirst) {
                $path[] = $categoryFirst->getId();
            } else return;
        }

        //echo 'getName '.$categoryFirst->getName().' '.$categoryFirst->getId()."\r\n\r\n";
        //second Sex

        $sex = $this->getSexProduct($forsageData);
        //echo '$sex = ' . $sex . "\r\n";
        if ($sex != '') {
            $level = 3;

            $nameCategory = $sex;
            switch ($mainCategoryForsageName) {
                case 'Обувь':
                    switch ($sex) {
                        case 'женщины':
                            $nameCategory = 'Женская обувь';
                            break;
                        case 'мужчины':
                            $nameCategory = 'Мужская обувь';
                            break;
                        case 'дети':
                            $nameCategory = 'Детская обувь';
                            break;
                    }
                    break;
                default:
                    $nameCategory = $sex;
                    break;

            }

            $categorySecond = $this->getCategoryByLevel($level, false, $nameCategory);
            //echo 'nameCategory'.$nameCategory." categorySecond=".$categorySecond->getId()."\r\n";
            if (!empty($categorySecond->getId())) {
                $path[] = $categorySecond->getId();

            } else {
                $data = [];
                $data['name'] = $nameCategory;
                $data['parent_id'] = $categorySecond->getId();
                $data['forsage_id'] = false;
                $data['path'] = $path;


                $categorySecond = $this->createCategory($data);
                if ($categorySecond) {
                    $path[] = $categorySecond->getId();
                } else return;
            }

            $level = 4;
            if (isset($forsageData->category->child)) {
                $childCategoryForsageId = $forsageData->category->child->id;
                $childCategoryForsageName = $forsageData->category->child->name;

                //echo '$level = ' . $level . ' ' . $childCategoryForsageId . ' ' . $childCategoryForsageName . "\r\n";
                if ($categorySecond->getId())
                    $categoryThree = $this->getCategoryByLevel($level, $childCategoryForsageId, $childCategoryForsageName, $categorySecond->getId());//, $childCategoryForsageName);
                else $categoryThree = $this->getCategoryByLevel($level, $childCategoryForsageId, $childCategoryForsageName, $categorySecond->getId());//, $childCategoryForsageName);
                if (!empty($categoryThree->getId())) {
                    $path[] = $categoryThree->getId();

                } else {
                    $data = [];
                    $data['name'] = $childCategoryForsageName;
                    $data['parent_id'] = $categorySecond->getId();
                    $data['forsage_id'] = $childCategoryForsageId;
                    $data['path'] = $path;


                    $categoryThree = $this->createCategory($data);
                    if ($categoryThree) {
                        $path[] = $categoryThree->getId();
                    } else return;
                }
            }


        } else {

            $level = 3;
            $categorySecond = $this->getCategoryByLevel($level, false, 'Разное');
            if (!empty($categorySecond->getId())) {
                $path[] = $categorySecond->getId();

            }

        }

//        echo '<pre>$path';
//        print_r($path);
//        echo '</pre>';
        if (count($path) > 0)
            return $path[count($path) - 1];

        return false;
    }

    public function createCategory($data)
    {
        $category_factory = $this->categoryFactory;

// Add a new sub category under root category
        $catName = ucfirst($data['name']);
        $path = $data['path'];
        $parentId = $data['parent_id'];
        $forsageId = $data['forsage_id'];

        $url = $this->additionalHelper->create()->translite($catName);
        $site_url = strtolower($url);
        $clean_url = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($site_url))))));


        $category_obj = $category_factory->create();
        $category_obj->setName($catName);
        $category_obj->setIsActive(true);

        $category_obj->setUrlKey($clean_url);
        $category_obj->setParentId($parentId);
        $store_Id = 1;

//        echo '$catName'.$catName;
//        echo '<pre>$data';
//        print_r($data);
//        echo '</pre>';
        //$category_obj->setData('forsage_id', $forsageId);
        $category_obj->setStoreId($store_Id);
        if (is_array($path))
            $category_obj->setPath(implode('/', $path));
// save category
        if ($category_obj->save()) {
            $_category = $this->categoryFactory->create()->load($category_obj->getId());

            $_category->setData('forsage_id', $forsageId);//ForsageId($forsageId);

            $_category->save();
//            echo '$_category =' .$_category->getId().' '.$category_obj->getId();
//            die();
            return $_category;
        }


        //return $category;
    }

    /**
     * @param $level
     * @param $forsageId
     * @return \Magento\Framework\DataObject
     */
    public function getCategoryByLevel($level, $forsageId = false, $name = false, $parentId = false)
    {
        $categoriesCollection = $this->categoriesFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        //$categories->addAttributeToFilter('level', $level);//['gt' => 0]);
        $category = $categoriesCollection
            ->addFilter('level', ['eq' => $level]);
        if ($parentId)
            $category->addAttributeToFilter('parent_id', ['eq' => $parentId]);

        if ($name)
            $category->addAttributeToFilter('name', ['eq' => $name]);
        //$category->addFilter('name', ['eq' => $name]);

        if ($forsageId)
            $category->addAttributeToFilter('forsage_id', ['eq' => $forsageId]);
        //$category->addFilter('forsage_id', ['eq' => $forsageId]);


        return $category->getFirstItem();

    }

    public function processForsage($input)
    {
//        echo '<pre>$input';
//        print_r($input);
//        echo '</pre>';


        $connection = $this->getConnection();


        if (isset($input['product_ids'])) {
            $products = $input['product_ids'];

            foreach ($products as $product_id) {
                //echo $product_id . "\r\n";
                $product_request = $connection->GetProduct($product_id);
                if ($product_request->success) {
                    $productForsage = $product_request->product;

                    $this->processForsageData($productForsage);
                    /*echo '<pre>';
                    print_r($product_request);
                    echo '</pre>';
                    die();*/
                }

            }

        }


    }

    protected function getPrice($forsageData)
    {

        $price = 0;
        $paramsForsge = 25;
        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == $paramsForsge) {
                $_price = $characteristic->value;
            }

        }
        $price = $_price;
        $currency = 'грн';

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 35) {
                $currency = $characteristic->value;
            }

        }

        $par_inbox = 1;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 8) {
                $par_inbox = (int)$characteristic->value;
            }

        }

        if ($currency == 'доллар') {
            $currencyFrom = 'USD';
            $currencyTo = 'UAH';

            $_rate = $this->currencyFactory->create()->load((string)$currencyTo);
            $rate = $_rate->getRate((string)$currencyFrom);
            $price = (float)$_price * $rate;
        }

        if ($price > 0)
            $price = $par_inbox * $price;

        return $price;


    }

    protected function setNews($forsageData, $product)
    {
        $photoses = $forsageData->photosession_date;

        $new = time();
        $datediff = $new - $photoses;
        $diff = floor($datediff / (60 * 60 * 24));

        if ($diff <= 15) {


            $new_from_date = date("m/d/Y h:i:s", $photoses);
            $new_to_date = date('m/d/Y h:i:s', strtotime("+15 day", $photoses));

            $product->setData('news_from_date', $new_from_date);
            $product->setData('news_to_date', $new_to_date);
            $product = $this->productRepository->create()->save($product);


        }
        return $product;


    }

    protected function getSupplier($forsageData)
    {
        $supplierForsage = $forsageData->supplier;
        $companyName = (string)$supplierForsage->company;

        $data = [];
        $data['code'] = 'postavschik';
        $data['name'] = 'Поставщик';
        $data['value'] = $companyName;
        $data['type'] = 'select';
        $attribute = $this->searchAttribute($data);

        if (!$attribute) {
            $attribute = $this->createProductAttribute($data, [$companyName]);

        }


        $valueMagento = $this->searchOptionByAttributeAndOption($companyName, 'postavschik');//, $attribute);

        if (!$valueMagento) {

            $this->addOption($attribute->getAttributeId(), $companyName);
            $valueMagento = $this->searchOptionByAttributeAndOption($companyName, 'postavschik');//$attribute->getAttributeCode(), $attribute);

        }


        return $valueMagento;


    }

    protected function getName($forsageData)
    {
        $supplierForsage = $forsageData->supplier;
        $companyName = (string)$supplierForsage->company;
        $sex = $this->getSexProduct($forsageData);
        if (isset($forsageData->category->child)) {
            $type = $forsageData->category->child->name;


            switch ($sex) {
                case 'женщины':
                    $nameSex = 'женские';
                    break;
                case 'мужчины':
                    $nameSex = 'мужские';
                    break;
                case 'дети':
                    $nameSex = 'детские';
                    break;
                default:
                    $nameSex = $sex;
                    break;
            }
            //Тайтл = Поставщик + артикул товара(не айди от форсажа) +тип + пол ?
            $name = $type . ' ' . $nameSex . ' ' . $companyName . ' ' . (string)$forsageData->vcode;
            return $name;
        }
        return $companyName . ' ' . (string)$forsageData->vcode;
    }

    protected function getBrand($forsageData)
    {
//        echo '<pre>';
//        print_r($forsageData);
//        echo '</pre>';
//        die();
        if (isset($forsageData->brand->name)) {


            $name = $forsageData->brand->name;

            $data = [];
            $data['code'] = 'brand';
            $data['name'] = 'Бренд';
            $data['value'] = $name;
            $data['type'] = 'select';
            $attribute = $this->searchAttribute($data);

            if (!$attribute) {
                $attribute = $this->createProductAttribute($data, [$name]);

            }


            $valueMagento = $this->searchOptionByAttributeAndOption($name, 'brand');//, $attribute);

            if (!$valueMagento) {

                $this->addOption($attribute->getAttributeId(), $name);
                $valueMagento = $this->searchOptionByAttributeAndOption($name, 'brand');

            }


            return $valueMagento;

        }
        return false;
    }


    public function getNameFoto($filename)
    {
        $path = explode('/', $filename);
        if (isset($path[count($path) - 1])) {
            $name_ar = explode('.', $path[count($path) - 1]);
            if (isset($name_ar[0]))
                return $name_ar[0];
        }
        return 'temp';
    }

    public function getExtension1($filename)
    {
        $array = explode(".", $filename);
        return end($array);
    }

    public function downloadPhoto($photo, $imageName)
    {
        $contentfile = @file_get_contents($photo);
        //echo '$photo '.$photo."\r\n";
        if ($contentfile != '') {
            $extension = $this->getExtension1($photo);
            $imageName = str_replace('/', '', $imageName);
            $imageName = str_replace("'", '_', $imageName);
            $imageName = str_replace('"', '_', $imageName);
            $local = $this->_tmpDirectory->getAbsolutePath('tmp/' . $imageName . '.' . $extension);
            //$local = Mage::getBaseDir('media') . DS . 'tmp/' . $imageName . '.' . $extension;


            file_put_contents($local, $contentfile);
            return $local;
        }
        return false;

    }

    protected function uploadImages($forsageData)
    {

        $result_photo = [];
        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ((string)$characteristic->type == 'image') {
                $photo = (string)$characteristic->value;
                $imageName = $this->getNameFoto($photo);


                $download = $this->downloadPhoto($photo, $imageName . time() . '_2_1_2');

                if ($download) {
                    if ((string)$characteristic->name == 'Фото 1') {
                        $result_photo[0] = $download;
                    } elseif ((string)$characteristic->name == 'Фото 2') {
                        $result_photo[1] = $download;

                    } elseif ((string)$characteristic->name == 'Фото 3') {
                        $result_photo[2] = $download;
                    } else $result_photo[] = $download;

                }

            }
        }

        return $result_photo;
    }

    protected function setImages($forsageData, $product)
    {

        $data = $this->uploadImages($forsageData);
        $images = $product->getMediaGalleryImages();

        if ($product) {
            foreach ($images as $child) {
                $this->gallery->deleteGallery($child->getValueId());
                $this->galleryProcessor->removeImage($product, $child->getFile());
            }
        }
        foreach ($data as $key => $file) {
            // as $sku => $_da) {

//            if (!$product)
//            $product = $this->getProductBySku($sku);
//
            if ($product) {
                $this->readHandler->execute($product);


                //foreach ($_da as $key => $file) {

                if ($product) {

                    //echo '$file' . $file . "\r\n\r\n";
                    /**
                     * Add image. Image directory must be in ROOT/pub/media for addImageToMediaGallery() method to work
                     */
                    if (file_exists($file)) {
                        if ($key == 0)
                            $product->addImageToMediaGallery($file, array('image', 'small_image', 'thumbnail'), true, false);
                        else
                            $product->addImageToMediaGallery($file, array(), true, false);

                        $product->save();
                    }
                    //echo "Added media image for {$sku}" . "\n";


                }

                //}
            }

            ob_flush();
            flush();

        }

    }

    protected function getDescription($forsageData)
    {

        $description = '';

        $description = '<p> </p><ul>';
        $hars = [];
//
//        Цвет: голубой
//Материал изделия: джинс
//Размерная сетка: 36-41
//Повторные размеры: 38-2.39-2
//Пар в ящике: 8
//Материал подошвы: резина
//Страна: Украина
//Высота платформы: 1,5
//Сезон: демисезон
//Валюта продажи: гривна
//Пол: женские
        $characteristics = $forsageData->characteristics;

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 4) {
                if ((string)$characteristic->value != '')
                    $hars['Цвет'] = $characteristic->value;
            }

        }


        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 5) {
                if ((string)$characteristic->value != '')
                    $hars['Материал изделия'] = $characteristic->value;
            }

        }
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 6) {
                if ((string)$characteristic->value != '')
                    $hars['Размерная сетка'] = $characteristic->value;
            }

        }
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 8) {
                if ((string)$characteristic->value != '')
                    $hars['Пар в ящике'] = $characteristic->value;
            }

        }
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 10) {
                if ((string)$characteristic->value != '') {
                    $hars['Материал подошвы'] = (string)$characteristic->value;
                }
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 11) {
                if ((string)$characteristic->value != '') {
                    $hars['Страна'] = (string)$characteristic->value;
                }
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 16) {
                if ((string)$characteristic->value != '') {
                    $hars['Высота платформы'] = (string)$characteristic->value;
                }
            }

        }


        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 28) {
                if ((string)$characteristic->value != '') {
                    $hars['Сезон'] = (string)$characteristic->value;
                }
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 35) {
                if ((string)$characteristic->value != '') {
                    $hars['Валюта продажи'] = (string)$characteristic->value;
                }
            }

        }
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 39) {
                if ((string)$characteristic->value != '') {
                    $hars['Пол'] = (string)$characteristic->value;
                }
            }

        }


        foreach ($hars as $har => $value)
            $description .= '<li><span class="label">' . $har . ': <span class="value">' . $value . '</span></span></li>';
        $description = $description . '</ul><p> </p>';


        return $description;
    }

    protected function getSpecialPrice($forsageData, $product)
    {

    }

    protected function getShortDescription($forsageData)
    {
        $description = '';


        $characteristics = $forsageData->characteristics;
        $hars_short = [];

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 8) {
                if ((string)$characteristic->value != '')
                    $hars['Количество'] = $characteristic->value . ' шт / упаковка';
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 6) {
                if ((string)$characteristic->value != '')
                    $hars['Размерная сетка'] = $characteristic->value;
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 28) {
                if ((string)$characteristic->value != '') {
                    $hars['Сезон'] = (string)$characteristic->value;
                }
            }

        }

        if (isset($forsageData->brand->name))
            $hars['Бренд'] = (string)$forsageData->brand->name;

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 10) {
                if ((string)$characteristic->value != '') {
                    $hars['Материал подошвы'] = (string)$characteristic->value;
                }
            }

        }


        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 5) {
                if ((string)$characteristic->value != '')
                    $hars['Материал изделия'] = $characteristic->value;
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 9) {
                if ((string)$characteristic->value != '')
                    $hars['Материал подкладки'] = $characteristic->value;
            }

        }


        $description = '<table><tbody>';
        foreach ($hars as $h => $v) {
            if (trim($v) != '' and trim($v) != 'шт / упаковка')
                $description .= '<tr style="height: 18px;"><th style="height: 18px; width: 176.932px;">' . $h . ':</th><td class="attr_product_td" style="height: 18px; width: 116.932px;">' . $v . '</td></tr>';
        }
        $description = $description . '</tbody></table>';

        return $description;
    }


    protected function setSpecialPrice($forsageData, $product)
    {
        $priceOldForsage = 0;
        $priceForsage = 0;
        $priceMagento = $product->getPrice();
        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 47) {
                if ((float)$characteristic->value != '')
                    $priceOldForsage = (float)$characteristic->value;
                //$hars['Материал изделия'] = $characteristic->value;
            }

        }

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 25) {
                if ((float)$characteristic->value != '')
                    $priceForsage = (float)$characteristic->value;
                //$hars['Материал изделия'] = $characteristic->value;
            }

        }


        $price = 0;
        $paramsForsge = 25;

        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == $paramsForsge) {
                $priceForsage = (float)$characteristic->value;
            }

        }

        $paramsForsge = 47;
        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == $paramsForsge) {
                $priceOldForsage = (float)$characteristic->value;
            }

        }
        //$price = $_price;
        $currency = 'грн';

        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 35) {
                $currency = $characteristic->value;
            }

        }

        $par_inbox = 1;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == 8) {
                $par_inbox = (int)$characteristic->value;
            }

        }

        if ($currency == 'доллар') {
            $currencyFrom = 'USD';
            $currencyTo = 'UAH';

            $_rate = $this->currencyFactory->create()->load((string)$currencyTo);
            $rate = (float)$_rate->getRate((string)$currencyFrom);
            //$price = $_price * $rate;
            $priceForsage = $priceForsage * $rate;
            $priceOldForsage = $priceOldForsage * $rate;
        }

        $priceOldForsage = $par_inbox * $priceOldForsage;
        $priceForsage = $par_inbox * $priceForsage;


        if ($priceForsage < $priceOldForsage) {
            $product->setPrice($priceOldForsage);
            $product->setSpecialPrice($priceForsage);

            $product->getResource()->saveAttribute($product, 'special_price');
            $product->getResource()->saveAttribute($product, 'price');
        } else {
            $product->setSpecialPrice(NULL);
            $product->getResource()->saveAttribute($product, 'special_price');


        }
        return $product;

    }


    public function processForsageData($forsageData)
    {

        $this->forsageLog = new \Monolog\Logger('forsage', [new \Monolog\Handler\StreamHandler(BP . '/var/log/forsage.log')]);

        $quantity = (int)$forsageData->quantity;
        $forsage_id = (string)$forsageData->id;
//        echo '$forsage_id'.$forsage_id.' '.$forsageData->category->id;
//        die();
        //if ((int)$forsageData->category->id != 9) {


        $product = $this->searchProductByForsageId($forsage_id);

        //if (!$product) {

        $categoryId = $this->getPathCategory($forsageData);
        $characteristics = $this->processCharacteristics($forsageData);
        $articul = $forsageData->vcode;


        $price = $this->getPrice($forsageData);
        $name = $this->getName($forsageData);

        $supplierMagento = $this->getSupplier($forsageData);
        $manufacture = $this->getBrand($forsageData);

        $supplier = $forsageData->supplier;


        $description = $this->getDescription($forsageData);
        $shortDescription = $this->getShortDescription($forsageData);


        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
//            $eavSetup->removeAttribute(
//                \Magento\Catalog\Model\Product::ENTITY,
//                'staraya_cena_prodazhi');
//
//            $eavSetup->removeAttribute(
//                \Magento\Catalog\Model\Product::ENTITY,
//                'staraya_cena_zakupki');


//                echo '$quantity'.$quantity;
//                die();
        if (!$product) {


            if ($quantity > 0) {
                $product = $this->productFactory->create();
                $product->setData($characteristics);
                $product->setShortDescription($shortDescription);
                $product->setDescription($description);

                $product->setSku($forsage_id);
                $product->setName($name);
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setVisibility(4);
                $product->setArticul($articul);


                $product->setPrice($price);
                $product->setAttributeSetId(4); // Default attribute set for products
                //if ($quantity > 0)
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
//                        else
//                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

                $product->setCustomAttribute('tax_class_id', 0);
                echo 'getSku = ' . $product->getSku() . ' ' . $forsage_id . "\r\n\r\n";


                $product->setCategoryIds([$categoryId]);

                if ($manufacture) $product->setBrand($manufacture);

                $product->setAddress((string)$supplier->address);
                $product->setCompany((string)$supplier->company);
                $product->setEmail((string)$supplier->email);
                $product->setPhone((string)$supplier->phone);

                $product->setPostavschik($supplierMagento);


                $urlKey = $this->additionalHelper->create()->translite($name) . time() . '-' . $product->getSku() . $product->getId();
                $product->setUrlKey($urlKey);

                $product = $this->productRepository->create()->save($product);

                if ($quantity > 0) {
                    $isInStock = 1;
                    $stockQty = 999;
                } else {
                    $isInStock = 0;
                    $stockQty = 0;
                }


//                echo '<pre>$characteristics sku product ' . $product->getSku() . ' ' . $product->getId();
//                print_r($characteristics);
//                echo '</pre>';
//                echo 'categoryId = ' . $categoryId;
                $this->setImages($forsageData, $product);
                $this->setSpecialPrice($forsageData, $product);

                $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
                $stockItem->setIsInStock($isInStock);
                $stockItem->setQty($stockQty);
                $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

                $this->setNews($forsageData, $product);
                //$log = new \Monolog\Logger('forsage', [new \Monolog\Handler\StreamHandler(BP.'/var/log/forsage.log')]);
                $this->forsageLog->error('new product - ' . $product->getSku() . ' product_id' . $product->getId());


                //$this->logger->debug('new product - '.$product->getSku() . ' product_id' . $product->getId());
                //die();
            }

        } else {

            $product->setData($characteristics);
            $product->setShortDescription($shortDescription);
            $product->setDescription($description);
            $product->setSku($forsage_id);
            $product->setArticul($articul);
            $product->setName($name);

            //$urlKey = $this->additionalHelper->create()->translite($name).'-'.time();
            //$product->setUrlKey($urlKey);
            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            $product->setVisibility(4);
            $product->setPrice($price);
            $product->setAttributeSetId(4); // Default attribute set for products
            //if ($quantity > 0)
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
//                    else
//                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);


            $product->setCustomAttribute('tax_class_id', 0);
            echo 'getSku = ' . $product->getSku() . ' ' . $forsage_id . "\r\n\r\n";


            $product->setCategoryIds([$categoryId]);

            if ($manufacture) $product->setBrand($manufacture);

            $product->setAddress((string)$supplier->address);
            $product->setCompany((string)$supplier->company);
            $product->setEmail((string)$supplier->email);
            $product->setPhone((string)$supplier->phone);

            $product->setPostavschik($supplierMagento);


            $product = $this->productRepository->create()->save($product);


            //                echo '<pre>update product $characteristics sku product ' . $product->getSku() . ' ' . $product->getId();
            //                print_r($characteristics);
            //                echo '</pre>';
            //                echo 'categoryId = ' . $categoryId;

            //$this->setImages($forsageData, $product);
            $this->setNews($forsageData, $product);


            $this->setSpecialPrice($forsageData, $product);

            if ($quantity > 0) {
                $isInStock = 1;
                $stockQty = 999;
            } else {
                $isInStock = false;
                $stockQty = 0;
            }

            //                $isInStock = 1;
            //                $stockQty = 999;
            $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
            $stockItem->setIsInStock($isInStock);
            $stockItem->setQty($stockQty);
            $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
            $this->forsageLog->error('update product - ' . $product->getSku() . ' product_id' . $product->getId());
            //$this->logger->debug();
            //die();
        }


        if ($product) {
            $this->reindexByProductsIds([$product->getId()], ['catalog_product_category', 'catalog_product_attribute']);
        }

        //}

//        }

    }

    public function reindexByProductsIds($productIds, $indexLists)
    {
        foreach ($indexLists as $indexList) {
            $categoryIndexer = $this->indexerRegistry->get($indexList);
            if (!$categoryIndexer->isScheduled()) {
                $categoryIndexer->reindexList(array_unique($productIds));
            }
        }
    }

    protected function getImages($forsageData)
    {

    }


    public function searchProductByForsageId($forsageId)
    {
        try {
            $product = $this->productRepository->create()->get((string)$forsageId);
            return $product;

        } catch (NoSuchEntityException $e) {
            return false;
        }


    }

    public function updateProduct($product_id, $data)
    {

    }

    public function newProduct($data)
    {

    }

    public function getProductByForsageId()
    {

    }

    /**
     * @param $forsageData
     */
    protected function getSexProduct($forsageData)
    {
        $paramsForsge = self::PARAMS_SEX_FORSAGE;
        $characteristics = $forsageData->characteristics;
        foreach ($characteristics as $characteristic) {
            if ($characteristic->id == $paramsForsge) {
                return $characteristic->value;
            }

        }

        return false;

    }

    /**
     * @param $forsageData
     */
    protected function getMainCategoryMagento($forsageData)
    {

        if (isset($forsageData->category)) {
            $idForsage = $forsageData->category->id;
            $nameForsage = $forsageData->category->name;
        }

    }

    protected function searchMagentoCategory($data)
    {
        //$this->categoryRepository
    }

    /**
     * @param $forsageData
     */
    protected function getChildCategory($forsageData)
    {

    }


    protected function newCategory()
    {

    }

}