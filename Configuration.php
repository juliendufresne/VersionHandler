<?php
declare(strict_types = 1);
namespace JulienDufresne\VersionHandler;

use JulienDufresne\VersionHandler\Exception\ConfigurationException;

/**
 * Define all parameters for a configuration.
 * This is a Value Object so don't modify the properties.
 */
final class Configuration
{
    /**
     * @var string
     */
    public $file;
    /**
     * @var string
     */
    public $parameterKey;
    /**
     * @var string[]
     */
    public $strategies;

    /**
     * @param string   $file
     * @param string   $parameterKey
     * @param string[] $strategies
     */
    public function __construct(string $file, string $parameterKey, array $strategies)
    {
        $this->file         = $file;
        $this->parameterKey = $parameterKey;
        $this->strategies   = $strategies;
    }

    /**
     * @param string[] $config
     *
     * @return Configuration
     * @throws ConfigurationException
     */
    public static function fromArray(array $config)
    {
        if (empty($config['file'])) {
            throw new ConfigurationException(
                'The extra.juliendufresne-version.file setting is required to use this script handler.'
            );
        }

        if (!is_file($config['file'])) {
            throw new ConfigurationException(
                sprintf('The file "%s" does not exist. Check your file config or create it.', $config['file'])
            );
        }

        if (empty($config['parameter-key'])) {
            throw new ConfigurationException(
                'The extra.juliendufresne-version.parameter-key setting is required to use this script handler.'
            );
        }

        if (empty($config['strategies'])) {
            $config['strategies'] = ['git', 'incremental'];
        }

        return new static($config['file'], $config['parameter-key'], $config['strategies']);
    }
}
