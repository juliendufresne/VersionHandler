<?php
declare(strict_types = 1);
namespace JulienDufresne\VersionHandler;

use Composer\IO\IOInterface;
use JulienDufresne\VersionHandler\Exception\FileException;
use JulienDufresne\VersionHandler\Exception\StrategyException;
use JulienDufresne\VersionHandler\Strategy\GitVersionStrategy;
use JulienDufresne\VersionHandler\Strategy\IncrementalVersionStrategy;
use JulienDufresne\VersionHandler\Strategy\VersionStrategyInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

final class Processor
{
    /**
     * @var string[]
     */
    private static $strategyMap = [
        'git'         => GitVersionStrategy::class,
        'incremental' => IncrementalVersionStrategy::class,
    ];

    /**
     * @var IOInterface
     */
    private $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function processFile(Configuration $configuration)
    {
        $dump           = $this->parseFile($configuration->file);
        $currentVersion = $this->findVersion($dump, $configuration->parameterKey);
        $newVersion     = '';
        $strategyUsed   = null;
        foreach ($configuration->strategies as $strategy) {
            /**
             * @var VersionStrategyInterface $class
             */
            $class      = new static::$strategyMap[$strategy]($this->io);
            $newVersion = $class->process($currentVersion);
            if ('' !== $newVersion) {
                $strategyUsed = $strategy;
                break;
            }
        }

        if ('' === $newVersion) {
            throw new StrategyException('Unable to set a valid version value.');
        }

        if ($currentVersion === $newVersion) {
            $this->io->write('New version match current version. Moving on');

            return;
        }

        $this->io->write(
            [
                sprintf('Found a new version using strategy <info>%s</info>', $strategyUsed),
                sprintf('* Previous: %s', '' === $currentVersion ? '-' : $currentVersion),
                sprintf('* New: %s', $newVersion),
            ]
        );

        $dump = $this->updateVersion($dump, $configuration->parameterKey, $newVersion);
        $this->dumpFile($configuration->file, $dump);
    }

    /**
     * Reads and parses file with parameters
     *
     * @param string $filePath
     *
     * @return mixed
     * @throws FileException
     */
    private function parseFile(string $filePath)
    {
        $yaml = new Parser();

        try {
            return $yaml->parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            throw new FileException(
                sprintf(
                    'Unable to load file "%s" content. Exception: %s',
                    $filePath,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param string   $file
     * @param string[] $dump
     */
    private function dumpFile(string $file, array $dump)
    {
        file_put_contents($file, Yaml::dump($dump, 99));
    }

    /**
     * @param string $parameterKey
     * @param array  $dump
     *
     * @return string
     */
    private function findVersion(array $dump, string $parameterKey) : string
    {
        if (array_key_exists($parameterKey, $dump)) {
            return $dump[$parameterKey];
        }

        if (false === strpos($parameterKey, '.')) {
            return '';
        }

        $keys = explode('.', $parameterKey, 2);

        return $this->findVersion($dump[$keys[0]], $keys[1]);
    }

    /**
     * @param array  $dump
     * @param string $parameterKey
     * @param string $newVersion
     *
     * @return array
     */
    private function updateVersion(array $dump, string $parameterKey, string $newVersion) : array
    {
        if (array_key_exists($parameterKey, $dump)) {
            $dump[$parameterKey] = $newVersion;

            return $dump;
        }

        if (false === strpos($parameterKey, '.')) {
            $dump[$parameterKey] = $newVersion;

            return $dump;
        }

        $keys = explode('.', $parameterKey, 2);

        $dump[$keys[0]] = $this->updateVersion($dump[$keys[0]], $keys[1], $newVersion);

        return $dump;
    }
}
