<?php
declare(strict_types=1);

namespace SomethingDigital\CmsMetaImage\Controller\Adminhtml\MetaImage;

use Magento\Backend\App\Action;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Cms::save';
    public const UPLOAD_DIR = 'wysiwyg/pagemeta';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Images
     */
    private $cmsWysiwygImages;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        UploaderFactory $uploaderFactory,
        DirectoryList $directoryList,
        Images $cmsWysiwygImages
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->cmsWysiwygImages = $cmsWysiwygImages;
    }

    public function execute(): Json
    {
        $fieldName = $this->getRequest()->getParam('param_name');
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);

        // Set our parameters
        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowedExtensions(['jpeg','jpg','png','gif']);
        $fileUploader->setAllowCreateFolders(true);

        try {
            if (!$fileUploader->checkMimeType(['image/png', 'image/jpeg', 'image/gif'])) {
                throw new LocalizedException(__('File validation failed.'));
            }

            $result = $fileUploader->save($this->getUploadDir());
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $result['id'] = $this->cmsWysiwygImages->idEncode($result['file']);
            $result['url'] = $baseUrl . $this->getFilePath($result['file']);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    private function getFilePath(string $imageName): string
    {
        return rtrim(self::UPLOAD_DIR, '/') . '/' . ltrim($imageName, '/');
    }

    private function getUploadDir(): string
    {
        return $this->directoryList->getPath(AppDirectoryList::MEDIA) . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
    }
}
