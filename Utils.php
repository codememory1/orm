<?php

namespace Codememory\Components\Database\Orm;

use Codememory\Components\Configuration\Configuration;
use Codememory\Components\Configuration\Interfaces\ConfigInterface;
use Codememory\Components\GlobalConfig\GlobalConfig;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Utils
 *
 * @package Codememory\Components\Database\Orm
 *
 * @author  Codememory
 */
class Utils
{

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Utils Construct
     */
    public function __construct()
    {

        $this->config = Configuration::getInstance()->open(GlobalConfig::get('orm.configName'), $this->defaultConfig());

    }

    /**
     * @return string
     */
    public function getPathWithEntities(): string
    {

        return $this->config->get('orm.pathWithEntities') . '/';

    }

    /**
     * @return string
     */
    public function getEntityNamespace(): string
    {

        return $this->config->get('orm.entityNamespace') . '\\';

    }

    /**
     * @return string
     */
    public function getPathWithRepositories(): string
    {

        return $this->config->get('orm.pathWithRepositories') . '/';

    }

    /**
     * @return string
     */
    public function getRepositoryNamespace(): string
    {

        return $this->config->get('orm.repositoryNamespace') . '\\';

    }

    /**
     * @return string
     */
    public function getEntitySuffix(): string
    {

        return $this->config->get('orm.entitySuffix');

    }

    /**
     * @return string
     */
    public function getRepositorySuffix(): string
    {

        return $this->config->get('orm.repositorySuffix');

    }

    /**
     * @return string[]
     */
    #[ArrayShape(
        [
            'pathWithEntities'     => "string",
            'entityNamespace'      => "string",
            'pathWithRepositories' => "string",
            'repositoryNamespace'  => "string",
            'entitySuffix'         => "string",
            'repositorySuffix'     => "string"
        ]
    )]
    private function defaultConfig(): array
    {

        return [
            'pathWithEntities'     => trim(GlobalConfig::get('orm.pathWithEntities'), '/'),
            'entityNamespace'      => trim(GlobalConfig::get('orm.entityNamespace'), '\\'),
            'pathWithRepositories' => trim(GlobalConfig::get('orm.pathWithRepositories'), '/'),
            'repositoryNamespace'  => trim(GlobalConfig::get('orm.repositoryNamespace'), '\\'),
            'entitySuffix'         => (string) GlobalConfig::get('orm.entitySuffix'),
            'repositorySuffix'     => (string) GlobalConfig::get('orm.repositorySuffix')
        ];

    }

}