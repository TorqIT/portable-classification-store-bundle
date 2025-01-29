<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyGroupRelation;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use Pimcore\Model\Exception\NotFoundException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorqIT\TorqITPortableClassificationStoreBundle\Services\ImportStoreService;
use TorqIT\TorqITPortableClassificationStoreBundle\TorqITPortableClassificationStoreBundle;

class ImportClassificationStoreCommand extends AbstractCommand
{
    private const STORE_NAME = 'store_name';

    public function __construct(private ImportStoreService $importStoreService)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('torqit:class-store:import')
            ->setDescription('Generates a classification store layout')
            ->addArgument(
                self::STORE_NAME,
                InputArgument::REQUIRED,
                'The store name to look for in previously saved exports.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::STORE_NAME);

        $fileName = str_replace(" ", "_", $input->getArgument(self::STORE_NAME)) . ".json";
        $fileName = TorqITPortableClassificationStoreBundle::getConfigPath() . "/$fileName";

        if (!file_exists($fileName)) {
            throw new NotFoundException("Could not find saved export for store '$name'. Looked in '$fileName'");
        }
        $data = json_decode(file_get_contents($fileName), true);

        $progress = null;

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {
            $progress = new ProgressBar($output, count($data));
        }

        $this->importStoreService->importStoreData($data, $name, $progress);

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
