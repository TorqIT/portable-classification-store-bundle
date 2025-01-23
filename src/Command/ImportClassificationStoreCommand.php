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
use TorqIT\TorqITPortableClassificationStoreBundle\TorqITPortableClassificationStoreBundle;

class ImportClassificationStoreCommand extends AbstractCommand
{
    private const STORE_NAME = 'store_name';

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
        $store = StoreConfig::getByName($name);

        if (!$store) {
            $store = new StoreConfig();
            $store->setName($name);
            $store->save();
        }

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

        foreach ($data as $key => $keyDefinition) {
            $this->upsertKeyAndGroups($store->getId(), $key, $keyDefinition);
            $progress?->advance();
        }

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln('');
        }

        return self::SUCCESS;
    }

    private function upsertKeyAndGroups(int $storeId, string $keyName, $payload)
    {
        if (!$keyConfig = KeyConfig::getByName($keyName, $storeId)) {
            $keyConfig = new KeyConfig();
            $keyConfig->setName($keyName);
            $keyConfig->setEnabled(true);
            $keyConfig->setStoreId($storeId);

            $this->output->writeln("Creating new key: $keyName", OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $this->output->writeln("Updating key: $keyName", OutputInterface::VERBOSITY_VERBOSE);
        }

        $keyConfig->setTitle(key_exists('title', $payload) ? $payload['title'] : $keyName);
        $keyConfig->setDescription(key_exists('description', $payload) ? $payload['description'] : '');
        $keyConfig->setType($payload['type']);
        $keyConfig->setDefinition($payload['definition']);

        $keyConfig->save();

        foreach ($payload['groups'] as $keyGroup) {
            if (!$groupObj = GroupConfig::getByName($keyGroup, $storeId)) {
                $groupObj = new GroupConfig();
                $groupObj->setName($keyGroup);
                $groupObj->setStoreId($storeId);

                $groupObj->save();
            }

            $rel = new KeyGroupRelation();
            $rel->setKeyId($keyConfig->getId());
            $rel->setGroupId($groupObj->getId());
            $rel->setSorter(0);

            $rel->save();
        }
    }
}
