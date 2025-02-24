<?php

namespace TorqIT\TorqITPortableClassificationStoreBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;

class TorqITPortableClassificationStoreBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;

    public function getJsPaths(): array
    {
        return [
            '/bundles/torqitportableclassificationstore/js/pimcore/startup.js'
        ];
    }

    public static function getConfigPath()
    {
        $configDir = implode(DIRECTORY_SEPARATOR, array(PIMCORE_PRIVATE_VAR, 'bundles', 'TorqITPortableClassificationStoreBundle'));
        if (!is_dir($configDir) && !mkdir($configDir, 0755, true) && !is_dir($configDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $configDir));
        }
        return $configDir;
    }
}
