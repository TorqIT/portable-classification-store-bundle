<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Services;

use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyGroupRelation;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportStoreService
{
    public function importStoreData(array $storeData, int $storeId, ?ProgressBar $progress = null)
    {
        if (!$store = StoreConfig::getById($storeId)) {
            throw new \Exception("Classification store id: $storeId not found.");
        }

        foreach ($storeData as $key => $keyDefinition) {
            $this->upsertKeyAndGroups($store->getId(), $key, $keyDefinition);
            $progress?->advance();
        }
    }

    private function upsertKeyAndGroups(int $storeId, string $keyName, $payload)
    {
        if (!$keyConfig = KeyConfig::getByName($keyName, $storeId)) {
            $keyConfig = new KeyConfig();
            $keyConfig->setName($keyName);
            $keyConfig->setEnabled(true);
            $keyConfig->setStoreId($storeId);
        }

        $keyConfig->setTitle(key_exists('title', $payload) ? $payload['title'] : $keyName);
        $keyConfig->setDescription(key_exists('description', $payload) ? $payload['description'] : '');
        $keyConfig->setType($payload['type']);
        $keyConfig->setDefinition($payload['definition']);
        $keyConfig->setEnabled(true);

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
