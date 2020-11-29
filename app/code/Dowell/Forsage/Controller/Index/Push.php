<?php

namespace Dowell\Forsage\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

use Magento\Framework\App\CsrfAwareActionInterface;

use Magento\Framework\App\Request\InvalidRequestException;

/**
 * Push Controller.
 */

class Push extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $helperForsage;
    protected $forsageLog;
    protected $requestHttp;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Dowell\Forsage\Helper\Data $helperForsage,
        \Magento\Framework\App\Request\Http $requestHttp
    )
    {
        $this->requestHttp=$requestHttp;
        $this->helperForsage=$helperForsage;
        $this->resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $this->forsageLog = new \Monolog\Logger('forsage', [new \Monolog\Handler\StreamHandler(BP . '/var/log/forsage_push.log')]);

        $content = $this->requestHttp->getContent();
            //$this->request->getContent();
        //$this->getRequest()->getContent();//->getPost();
//        echo '<pre>$content';
//        print_r($content);
//        echo '</pre>';

        $json = $this->getRequest()->getContent();
        $data = json_decode($json);
    //        echo '<pre>$json';
    //        print_r($json);
    //        echo '</pre>';
//            echo '<pre>$data';
//            print_r($data);
//            echo '</pre>';


        $phpInput=file_get_contents('php://input');


        $this->forsageLog->error('phpInput ='.$phpInput);
        $input = json_decode($phpInput, true);

        $this->helperForsage->processForsage($input);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData(['content' => '']);

    }

}
