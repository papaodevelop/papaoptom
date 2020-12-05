<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Imagecount extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_PageSpeedOptimizer::config';

    /**
     * @var ForceOptimization
     */
    private $forceOptimization;

    public function __construct(
        ForceOptimization $forceOptimization,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->forceOptimization = $forceOptimization;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        return $resultRaw->setContents($this->forceOptimization->getFilesCount());
    }
}
