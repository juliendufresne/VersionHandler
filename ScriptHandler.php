<?php
/**
 *
 */
namespace JulienDufresne\VersionHandler;

use Composer\Package\Package;
use Composer\Script\Event;
use JulienDufresne\VersionHandler\Exception\InvalidArgumentException;

final class ScriptHandler
{
    public static function updateVersion(Event $event)
    {
        /**
         * @var Package $package
         */
        $package = $event->getComposer()->getPackage();
        $extras  = $package->getExtra();
        $configs = $extras['juliendufresne-version'];

        if (!is_array($configs)) {
            throw new InvalidArgumentException(
                'The extra.juliendufresne-version setting must be an array or a configuration object.'
            );
        }

        if (array_keys($configs) !== range(0, count($configs) - 1)) {
            $configs = [$configs];
        }

        $processor = new Processor($event->getIO());

        foreach ($configs as $config) {
            if (!is_array($config)) {
                throw new InvalidArgumentException(
                    'The extra.juliendufresne-parameters setting must be an array of configuration objects.'
                );
            }

            $processor->processFile(Configuration::fromArray($config));
        }
    }
}
