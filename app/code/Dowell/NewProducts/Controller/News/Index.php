<?php
/**
 * Created by PhpStorm.
 * User: portnovvit
 * Date: 19.08.2020
 * Time: 12:07
 */
namespace Dowell\NewProducts\Controller\News;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Registry;
use Magento\Framework\Phrase;

use Dowell\Campaign\Model\ResourceModel\EventCampaign\CollectionFactory;
/**
 * Webkul Marketplace Account Dashboard Controller.
 */
class Index extends \Magento\Framework\App\Action\Action
{


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $redirectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;

        return parent::__construct($context);
    }


    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        if (!$this->_coreRegistry->registry('current_newProducts')) {
            $this->_coreRegistry->register('current_newProducts',true);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('News')
        );

        return $resultPage;
    }
}
