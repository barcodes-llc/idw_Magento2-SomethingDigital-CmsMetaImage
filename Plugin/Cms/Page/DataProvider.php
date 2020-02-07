<?php
declare(strict_types=1);

namespace SomethingDigital\CmsMetaImage\Plugin\Cms\Page;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use SomethingDigital\CmsMetaImage\Controller\Adminhtml\MetaImage\Upload;

class DataProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        \Magento\Cms\Model\Page\DataProvider $subject,
        array $result = []
    ) {
        if (empty($result)) {
            return $result;
        }

        foreach ($result as $idx => $item) {
            if (!$item['meta_image']) {
                continue;
            }

            $currentStore = $this->storeManager->getStore();
            $baseUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . Upload::UPLOAD_DIR;

            $imageName = $item['meta_image'];
            unset($item['meta_image']);

            $filePath = $this->getFilePath($imageName);

            if ($this->getMediaDirectory()->isExist($filePath)) {
                $item['meta_image'][0]['name'] = $imageName;
                $item['meta_image'][0]['file'] = $imageName;
                $item['meta_image'][0]['url'] = $baseUrl . '/' . $imageName;
                $item['meta_image'][0]['type'] = 'image';
                $item['meta_image'][0]['size'] = $this->getMediaDirectory()->stat($filePath)['size'] ?? 0;
            }

            $result[$idx] = $item;
        }

        return $result;
    }

    private function getFilePath(string $imageName): string
    {
        return rtrim(Upload::UPLOAD_DIR, '/') . '/' . ltrim($imageName, '/');
    }

    private function getMediaDirectory(): Filesystem\Directory\WriteInterface
    {
        if ($this->mediaDirectory) {
            return $this->mediaDirectory;
        }

        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        return $this->mediaDirectory;
    }
}
