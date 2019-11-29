<?php
declare(strict_types=1);

namespace SomethingDigital\CmsMetaImage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CmsPrepareSaveObserver implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $page = $event->getPage();
        $request = $event->getRequest();

        if (!$page || !$request) {
            return;
        }

        $metaImage = $request->getParam('meta_image');

        $page->setData('meta_image', $metaImage[0]['file'] ?? null);
    }
}
