<?php
namespace Papao\AmastyCustom\Helper;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_IS_SHOWSUBMIT_ONITEM_PATH = 'amshopby/general/submit_on_item';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function showSubmitOnItem() {
        return $this->scopeConfig->getValue(self::XML_IS_SHOWSUBMIT_ONITEM_PATH,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
