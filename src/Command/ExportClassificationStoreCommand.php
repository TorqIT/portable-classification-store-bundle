<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Command;

use Carbon\Carbon;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;

use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use Sabre\VObject\InvalidDataException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorqIT\TorqITPortableClassificationStoreBundle\Services\ExportStoreService;
use TorqIT\TorqITPortableClassificationStoreBundle\TorqITPortableClassificationStoreBundle;

class ExportClassificationStoreCommand extends AbstractCommand
{
    private const PRETTY_OPTION = 'pretty';
    private const STORE_NAME = 'store_name';

    public function __construct(
        private ExportStoreService $exportStoreService
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('torqit:class-store:export')
            ->setDescription('Creates an export for specified ClassificationStore')
            ->addOption(self::PRETTY_OPTION, 'p', InputOption::VALUE_NONE, 'Outputs prettified json')
            ->addArgument(self::STORE_NAME, InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $data = $this->exportStoreService->generateStoreData($input->getArgument(self::STORE_NAME));
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
            return self::FAILURE;
        }


        $storeName = str_replace(" ", "_", $input->getArgument(self::STORE_NAME));
        $fileName = "$storeName.json";
        @file_put_contents(TorqITPortableClassificationStoreBundle::getConfigPath() . "/$fileName", json_encode($data, $input->getOption(self::PRETTY_OPTION) ? JSON_PRETTY_PRINT : 0));;

        $this->output->writeln("ClassificationStore export saved.");

        return self::SUCCESS;
    }
}
