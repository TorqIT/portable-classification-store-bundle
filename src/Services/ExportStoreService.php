<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle\Services;

use Exception;
use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;

class ExportStoreService
{
    public function generateStoreData(int $storeId): string
    {
        $db = \Pimcore\Db::get();
        $sqlStatements = [];

        // 1. classificationstore_stores
        $storeRow = $db->fetchAssociative('SELECT * FROM classificationstore_stores WHERE id = ?', [$storeId]);
        if (!$storeRow) {
            throw new Exception("Classification store id: $storeId not found.");
        }
        $storeInsert = $this->buildInsertSQL('classificationstore_stores', $storeRow, false);
        $sqlStatements[] = $storeInsert;

        // 2. classificationstore_groups
        $groupRows = $db->fetchAllAssociative('SELECT * FROM classificationstore_groups WHERE storeId = ?', [$storeId]);
        foreach ($groupRows as $groupRow) {
            $sqlStatements[] = $this->buildInsertSQL('classificationstore_groups', $groupRow, false);
        }

        // 3. classificationstore_keys
        $keyRows = $db->fetchAllAssociative('SELECT * FROM classificationstore_keys WHERE storeId = ?', [$storeId]);
        foreach ($keyRows as $keyRow) {
            $sqlStatements[] = $this->buildInsertSQL('classificationstore_keys', $keyRow, false);
        }

        // 4. classificationstore_relations (key-group relations)
        foreach ($keyRows as $keyRow) {
            $relations = $db->fetchAllAssociative('SELECT * FROM classificationstore_relations WHERE keyId = ?', [$keyRow['id']]);
            foreach ($relations as $relationRow) {
                $sqlStatements[] = $this->buildInsertSQL('classificationstore_relations', $relationRow, false);
            }
        }

        // 5. classificationstore_collections
        $collectionRows = $db->fetchAllAssociative('SELECT * FROM classificationstore_collections WHERE storeId = ?', [$storeId]);
        foreach ($collectionRows as $collectionRow) {
            $sqlStatements[] = $this->buildInsertSQL('classificationstore_collections', $collectionRow, false);
        }

        // 6. classificationstore_collectionrelations (collection-group relations)
        foreach ($collectionRows as $collectionRow) {
            $relations = $db->fetchAllAssociative('SELECT * FROM classificationstore_collectionrelations WHERE colId = ?', [$collectionRow['id']]);
            foreach ($relations as $relationRow) {
                $sqlStatements[] = $this->buildInsertSQL('classificationstore_collectionrelations', $relationRow, false);
            }
        }

        return implode("\n", $sqlStatements);
    }

    /**
     * Helper to build an INSERT SQL statement from a row
     * @param string $table
     * @param array $row
     * @param bool $omitId (no longer used, always false)
     * @return string
     */
    private function buildInsertSQL(string $table, array $row, bool $omitId = false): string
    {
        $columns = array_keys($row);
        $values = array_map(function ($v) {
            if (is_null($v)) return 'NULL';
            if (is_bool($v)) return $v ? '1' : '0';
            return "'" . addslashes($v) . "'";
        }, array_values($row));
        return sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s);',
            $table,
            implode(', ', array_map(function ($c) { return "`$c`"; }, $columns)),
            implode(', ', $values)
        );
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
