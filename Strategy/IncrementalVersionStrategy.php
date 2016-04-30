<?php
/**
 *
 */
namespace JulienDufresne\VersionHandler\Strategy;

use Composer\IO\IOInterface;

final class IncrementalVersionStrategy implements VersionStrategyInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * GitVersionStrategy constructor.
     *
     * @param IOInterface $io
     */
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $currentVersion The version found in the file
     *
     * @return string The new version if the strategy is able to provide one.
     *                Returns an empty string otherwise.
     */
    public function process(string $currentVersion) : string
    {
        if ('' === $currentVersion) {
            return '1';
        }

        if (!preg_match('#^(.*\D?)(\d+)$#U', $currentVersion, $matches)) {
            $this->io->write('The current version ("%s") does not end with an integer value.');

            return '';
        }
        
        $versionNumber = (int)$matches[2];

        return sprintf('%s%d', $matches[1], ++$versionNumber);
    }
}
