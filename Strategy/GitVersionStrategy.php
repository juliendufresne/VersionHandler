<?php
declare(strict_types = 1);
namespace JulienDufresne\VersionHandler\Strategy;

use Composer\IO\IOInterface;
use Symfony\Component\Process\Process;

/**
 * Try to use git tag as version.
 */
final class GitVersionStrategy implements VersionStrategyInterface
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
        if (false === $this->isBinaryFound()) {
            $this->io->write('Git binary seems to be missing.', true, IOInterface::DEBUG);
            
            return '';
        }
        
        if (false === $this->isProjectUnderGit()) {
            $this->io->write('The project is not under git version control.', true, IOInterface::DEBUG);

            return '';
        }

        return $this->getCurrentTag() ?? '';
    }

    /**
     * Check whether the current environment has git installed or not.
     *
     * @return bool
     */
    private function isBinaryFound()
    {
        $process = new Process('which git');
        $process->run();
        
        return $process->isSuccessful();
    }

    /**
     * Performs a git status to ensure that the project is currently under git version control.
     * Note: we may need to use another way to check if the project is under git
     *       like checking if current directory contains a .git. Tell me if this is the case for you.
     * 
     * @return bool
     */
    private function isProjectUnderGit()
    {
        $process = new Process('git status');
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Gets current git version tag name
     * 
     * @return string|null The tag name if the current commit matches a tag, null otherwise.
     */
    private function getCurrentTag()
    {
        $process = new Process('git describe --exact-match HEAD');
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }
        
        return $process->getOutput();
    }
}
