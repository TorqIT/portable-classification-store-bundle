<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/upload")]
class UploadController extends AdminAbstractController
{
    #[Route("/", name: "pimcore_bundle_portalclassificationstore_upload", methods: ["POST"])]
    public function uploadAction(Request $request): Response
    {
        return $this->json(
            [
                "success" => true
            ]
        );
    }
}
