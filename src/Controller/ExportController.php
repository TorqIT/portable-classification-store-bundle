<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TorqIT\TorqITPortableClassificationStoreBundle\Services\ExportStoreService;

#[Route("/export")]
class ExportController extends AdminAbstractController
{
    #[Route("/{classificationstoreId}", name: "pimcore_bundle_portalclassificationstore_export", methods: ["GET"])]
    public function getExportAction(int $classificationstoreId, ExportStoreService $exportStoreService): Response
    {
        $exportData = json_encode(
            $exportStoreService->generateStoreData($classificationstoreId),
            JSON_PRETTY_PRINT
        );

        // Set headers to force download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="classificationstore_data.json"');
        header('Content-Length: ' . strlen($exportData));

        // Output the JSON data as a file
        echo $exportData;
        exit;
    }
}
