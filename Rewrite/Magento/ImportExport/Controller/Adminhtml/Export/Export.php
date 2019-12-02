<?php
/**
 * A Magento 2 module named UpMedio/ProductExport
 * Copyright (C) 2019.
 *
 * This file is part of UpMedio/ProductExport.
 *
 * UpMedio/ProductExport is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace UpMedio\ProductExport\Rewrite\Magento\ImportExport\Controller\Adminhtml\Export;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\ImportExport\Model\Export as ExportModel;
use Psr\Log\LoggerInterface;

/**
 * Class Export.
 */
class Export extends \Magento\ImportExport\Controller\Adminhtml\Export\Export
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;



    /**
     * @param Context                                                 $context
     * @param FileFactory                                             $fileFactory
     * @param \Magento\Framework\Session\SessionManagerInterface|null $sessionManager
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        SessionManagerInterface $sessionManager = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->sessionManager = $sessionManager ?: ObjectManager::getInstance()->get(SessionManagerInterface::class);

        parent::__construct($context, $fileFactory);
    }

    /**
     * Load data with filter applying and create file for download.
     *
     */
    public function execute()
    {
        if ($this->getRequest()->getPost(ExportModel::FILTER_ELEMENT_GROUP)) {
            try {
                $params = $this->getRequest()->getParams();

                /** @var ExportModel $model */
                $model = $this->_objectManager->create(ExportModel::class);
                $model->setData($params);
                $this->sessionManager->writeClose();

                return $this->fileFactory->create(
                    $model->getFileName(),
                    $model->export(),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (Exception $e) {
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
                $this->messageManager->addErrorMessage(__('Please correct the data sent value.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');

        return $resultRedirect;
    }
}
