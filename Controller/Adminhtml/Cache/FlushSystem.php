<?php
/**
 *
 * Copyright © 2015 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\AdvancedAcl\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action;
use ShopGo\AdvancedAcl\Model\Cache\Config as CacheConfig;

class FlushSystem extends \Magento\Backend\Controller\Adminhtml\Cache\FlushSystem
{
    /**
     * @var \ShopGo\AdvancedAcl\Model\Cache\Config
     */
    protected $_advAclModelCacheConfig;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CacheConfig $advAclModelCacheConfig
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CacheConfig $advAclModelCacheConfig
    ) {
        parent::__construct(
            $context,
            $cacheTypeList,
            $cacheState,
            $cacheFrontendPool,
            $resultPageFactory
        );

        $this->_advAclModelCacheConfig = $advAclModelCacheConfig;
    }

    /**
     * Flush all magento cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheDir = trim($cacheFrontend->getBackend()->getOption('cache_dir'), '/');
            $cacheDir = explode('/', $cacheDir);

            $access = $this->_advAclModelCacheConfig->getCachePageElementAccess([
                'types' => [],
                'type'  => [
                    'attributes' => [
                        'cache_dir' => $cacheDir[count($cacheDir) - 1]
                    ]
                ]
            ]);

            if ($access) {
                $cacheFrontend->clean();
            }
        }
        $this->_eventManager->dispatch('adminhtml_cache_flush_system');
        $this->messageManager->addSuccess(__("The Magento cache storage has been flushed."));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
