<?php

namespace Codememory\Components\Database\Orm;

use Codememory\Components\Caching\Cache;
use Codememory\Components\Caching\Exceptions\ConfigPathNotExistException;
use Codememory\Components\Caching\Interfaces\CacheInterface;
use Codememory\Components\Database\Orm\Interfaces\EntityDataInterface;
use Codememory\Components\Markup\Types\YamlType;
use Codememory\FileSystem\File;
use Codememory\FileSystem\Interfaces\FileInterface;

/**
 * Class EntityCache
 *
 * @package Codememory\Components\Database\Orm
 *
 * @author  Codememory
 */
class EntityCache
{

    public const TYPE_CACHE = '__cdm-orm';
    public const NAME_CACHE = '__entity_%s';

    /**
     * @var EntityDataInterface
     */
    private EntityDataInterface $entityData;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @param EntityDataInterface $entityData
     *
     * @throws ConfigPathNotExistException
     */
    public function __construct(EntityDataInterface $entityData)
    {

        $this->entityData = $entityData;
        $this->cache = new Cache(new YamlType(), new File());

    }

    /**
     * @return $this
     */
    public function createEntityCache(string $pathToEntity): EntityCache
    {

        $this->cache->create(
            self::TYPE_CACHE,
            $this->generateEntityCacheName($this->entityData->getEntityName()),
            $this->entityData->getEntity(),
            function (FileInterface $filesystem, string $fullPath) use ($pathToEntity) {
                $pathWithClass = sprintf('%s_class.cache', $fullPath);
                $entity = file_get_contents($filesystem->getRealPath($pathToEntity));
                $entity = preg_replace('/class\s([a-zA-Z_0-9]+)/', sprintf('class %s', $this->generateEntityCacheName('$1')), $entity);
                $entity = preg_replace('/namespace\s([^\s]+)/', '', $entity);

                file_put_contents($filesystem->getRealPath($pathWithClass), $entity);
            }
        );

        return $this;

    }

    /**
     * @return object
     */
    public function getEntityCache(): mixed
    {

        return $this->cache->get(
            self::TYPE_CACHE,
            $this->generateEntityCacheName($this->entityData->getEntityName()),
            function (FileInterface $filesystem, string $fullPath) {
                require sprintf('%s_class.cache', $fullPath);

                $entityName = $this->generateEntityCacheName($this->entityData->getEntityName());

                return new $entityName();
            }
        );

    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    public function generateEntityCacheName(string $entityName): string
    {

        return sprintf(self::NAME_CACHE, $entityName);

    }

}