<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Services;

use Exception;
use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;

class ExportStoreService
{
    public function generateStoreData(string $storeName): array
    {
        if (!$store = StoreConfig::getByName($storeName)) {
            throw new Exception("Classification Store with name $storeName not found");
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
                throw new Exception("Duplicate key names detected ({$key->getName()}). Keys cannot have the same name");
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
        return $data;
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
