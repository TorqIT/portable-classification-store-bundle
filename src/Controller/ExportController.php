<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TorqIT\TorqITPortableClassificationStoreBundle\Services\ExportStoreService;

#[Route("/export")]
class ExportController extends AdminAbstractController
{
    #[Route("/{classificationstoreId}", name: "pimcore_bundle_portalclassificationstore_export", methods: ["GET"])]
    public function getExportAction(int $classificationstoreId, ExportStoreService $exportStoreService): Response
    {
        if (!$store = StoreConfig::getById($classificationstoreId)) {
            return $this->json(['error' => 'Classification store not found'], Response::HTTP_NOT_FOUND);
        }
        $storeName = $store->getName();
        $exportData = $exportStoreService->generateStoreData($classificationstoreId);

        // Set headers to force download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $storeName . '.sql"');
        header('Content-Length: ' . strlen($exportData));

        // Output the JSON data as a file
        echo $exportData;
        exit;
    }
}
