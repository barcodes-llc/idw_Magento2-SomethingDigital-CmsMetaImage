<?php
declare(strict_types=1);

namespace SomethingDigital\CmsMetaImage\Block\OpenGraph;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use SomethingDigital\CmsMetaImage\Controller\Adminhtml\MetaImage\Upload;

class Image extends Template
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        Template\Context $context,
        Page $page,
        PageFactory $pageFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        // used singleton (instead factory) because there exist dependencies on \Magento\Cms\Helper\Page
        // this was taken from \Magento\Cms\Block\Page :(
        $this->page = $page;
        $this->pageFactory = $pageFactory;
    }

    public function getPage(): Page
    {
        if ($this->hasData('page')) {
            return $this->getData('page');
        }

        if ($this->getPageId()) {
            /** @var \Magento\Cms\Model\Page $page */
            $page = $this->pageFactory->create();
            $page->setStoreId($this->_storeManager->getStore()->getId())->load($this->getPageId(), 'identifier');
        } else {
            $page = $this->page;
        }
        $this->setData('page', $page);

        return $this->getData('page');
    }

    public function getMetaImageUrl(): string
    {
        $page = $this->getPage();
        $currentStore = $this->_storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . Upload::UPLOAD_DIR;

        return $baseUrl . '/' . $page->getMetaImage();
    }
}
