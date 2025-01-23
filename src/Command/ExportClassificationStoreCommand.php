<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Command;

use Carbon\Carbon;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use Sabre\VObject\InvalidDataException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorqIT\TorqITPortableClassificationStoreBundle\TorqITPortableClassificationStoreBundle;

class ExportClassificationStoreCommand extends AbstractCommand
{
    private const PRETTY_OPTION = 'pretty';
    private const STORE_NAME = 'store_name';

    public function configure()
    {
        $this->setName('torqit:class-store:export')
            ->setDescription('Creates an export for specified ClassificationStore')
            ->addOption(self::PRETTY_OPTION, 'p', InputOption::VALUE_NONE, 'Outputs prettified json')
            ->addArgument(self::STORE_NAME, InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $store = StoreConfig::getByName($input->getArgument(self::STORE_NAME));
        if (!$store) {
            $output->writeln('User gave incorrect store name');

            return self::INVALID;
        }
        $keys = (new KeyConfig\Listing())
            ->setCondition('storeId = ?', $store->getId())
            ->setOrderKey('name')
            ->load();

        $groups = (new GroupConfig\Listing())
            ->setCondition('storeId = ?', $store->getId())
            ->setOrderKey('name')
            ->load();

        $data = [];

        foreach ($keys as $key) {
            $keyGroups = [];

            foreach ($groups as $group) {
                if ($this->keyIsInGroup($key, $group)) {
                    $keyGroups[] = $group->getName();
                }
            }

            if (key_exists($key->getName(), $data)) {
                throw new InvalidDataException("Duplicate key names detected ({$key->getName()}). Keys cannot have the same name");
            }

            $keyData = [
                'type' => $key->getType(),
                'definition' => $key->getDefinition(),
                'groups' => $keyGroups
            ];

            if ($key->getName() != $key->getTitle()) {
                $keyData['title'] = $key->getTitle();
            }

            if ($key->getDescription()) {
                $keyData['description'] = $key->getDescription();
            }

            $data[$key->getName()] = $keyData;
        }

        $storeName = str_replace(" ", "_", $input->getArgument(self::STORE_NAME));
        $fileName = "$storeName.json";
        @file_put_contents(TorqITPortableClassificationStoreBundle::getConfigPath() . "/$fileName", json_encode($data, $input->getOption(self::PRETTY_OPTION) ? JSON_PRETTY_PRINT : 0));;

        $this->output->writeln("ClassificationStore export saved.");

        return self::SUCCESS;
    }

    private function keyIsInGroup(KeyConfig $key, GroupConfig $group)
    {
        foreach ($group->getRelations() as $keyGroupRelation) {
            if ($keyGroupRelation->getKeyId() == $key->getId()) {
                return true;
            }
        }

        return false;
    }
}
