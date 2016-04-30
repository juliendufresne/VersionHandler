<?php
declare(strict_types = 1);
namespace JulienDufresne\VersionHandler\Strategy;

/**
 * Basic requirements to define a strategy 
 */
interface VersionStrategyInterface
{
    /**
     * @param string $currentVersion The version found in the file
     *
     * @return string The new version if the strategy is able to provide one.
     *                Returns an empty string otherwise.
     */
    public function process(string $currentVersion) : string;
}
