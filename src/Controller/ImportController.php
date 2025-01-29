<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TorqIT\TorqITPortableClassificationStoreBundle\Services\ImportStoreService;

#[Route("/import")]
class ImportController extends AdminAbstractController
{
    #[Route("/{classificationstoreId}", name: "pimcore_bundle_portalclassificationstore_import", methods: ["POST"])]
    public function uploadAction(int $classificationstoreId, Request $request, ImportStoreService $importStoreService): Response
    {
        /** @var UploadedFile $classificationStoreImportFile */
        $classificationStoreImportFile = $request->files->get("classificationStoreImportFile");
        if (!$classificationStoreImportFile) {
            throw new \InvalidArgumentException('No file uploaded');
        }

        $fileContents = file_get_contents($classificationStoreImportFile);
        $importJsonData = json_decode($fileContents, true);

        $importStoreService->importStoreData($importJsonData, $classificationstoreId);

        return $this->json(
            [
                "success" => true
            ]
        );
    }
}
