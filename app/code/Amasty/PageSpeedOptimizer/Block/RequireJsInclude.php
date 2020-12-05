<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Block;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class RequireJsInclude extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Registry $registry,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * @return mixed|string
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled() || !$this->configProvider->isMoveJS()) {
            return '';
        }

        return $this->registry->registry('requireJsScript');
    }
}
