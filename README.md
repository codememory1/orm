# ORM

## Установка

```
composer require codememory/orm
```

> Обязательно выполнить команды после установки

* Создать глобальную конфигурацию, если ее не существует
    * `php vendor/bin/gc-cdm g-config:init`
* Merge всей конфигурации
    * `php vendr/bin/gc-cdm g-config:merge --all`

## Обзор конфигурации
```yaml
# configs/database.yaml

database:
  orm:
    pathWithEntities: 'App/Entities'  # Path with entities
    entityNamespace: 'App\Entities'  # Entity Namespace
    entitySuffix: 'Entity'            # Suffix for class|file
    
    # The same as with entities, only with repositories
    pathWithRepositories: 'App/Repositories'
    repositoryNamespace: 'App\Repositories'
    repositorySuffix: 'Repository'
```

## Пример инициализации

```php
<?php

use Codememory\Components\Database\Orm\EntityManager;

require_once 'vendor/autoload.php';

// Documentation by database connection https://github.com/codememory1/database-connection
$entityManager = new EntityManager($connector);
```

## Методы EntityManager
* `commit(): EntityManagerInteface` Добавить сущность в commit
    * object **$entity**


* `flush(): EntityManagerInterface` Добавить все записи из commits в таблицу


* `getRepository(): EntityRepositoryInterface` Получить репозиторий сущности
    * string|object **$entity**


* `isEntity(): bool` Проверить, является ли объект или namespace сущностью
    * string|object **$entity**


* `isExistEntityRepository(): bool` Проверить существование репозитория для сущности
    * string|object **$entity**


* `getEntityData(): EntityDataInterface` Получить объект данных сущности
    * string|object **$entity**

## Обзор сущности
```php
<?php

namespace App\Entities;

use Codememory\Components\Database\Orm\Constructions as ORM;
use App\Repositories\UserRepository;

#[ORM\Entity(tableName: 'users')]
#[ORM\Repository(repository: UserRepository::class)]
class UserEntity
{

    #[ORM\Column(name: 'id', type: 'int', nullable: false)]
    #[ORM\Identifier]
    private ?int $id = null;
    
    #[ORM\Column(name: 'username', type: 'varchar', length: 100, nullable: false)]
    private ?string $username = null;
    
    public function getId(): ?int
    {
    
        return $this->id;
    
    }
    
    public function setUsername(?string $username): UserEntity
    {
    
        $this->username = $username;
        
        return $this;
    
    }
    
    public function getUsername(): ?string
    {
    
        return $this->username;
    
    }

}
```

## Обзор репозитория

```php
<?php

namespace App\Repositories;

use Codememory\Components\Database\Orm\Repository\AbstractEntityRepository;

class UserRepository extends AbstractEntityRepository
{
    
    public function getUserByUniqueName(): array
    {
    
        $queryBuilder = $this->createQueryBuilder();
        
        $queryBuilder
            ->customSelect()
            ->distinct()
            ->columns(['username'])
            ->from('users');
            
        return $queryBuilder->generateQuery()->getResultAsEntity();
    }
    
}
```

## Список команд
* `db:check-connectin` Проверить подключение к БД
* `db:create-db` Создать БД подключенного юзера, если ее не существует
* `db:create-table {entity-name}` Создать таблицу сущности
* `db:update-table {entity-name}` Обновить структуру таблицы сущности
* `db:drop-table {entity-name}` Удалить таблицу сущности
* `db:db:list-entities` Список сущностей
* `db:db:list-repositories` Список репозиториев
* `make:entity {entity-name-without-suffix}` Создать сущность

## Список конструкций для сущности
> namespace constructions - `Codememory\Components\Database\Orm\Constructions`
* `AI` AUTO_INCREMENT
* `Collate` COLLATE with CHARACTER
* `DefaultValue` DEFAULT
* `Entity` *Doesn't apply to SQL*
* `Identifier` AUTO_INCREMENT PRIMARY KEY
* `Join` *Doesn't apply to SQL*
* `Primary` PRIMARY KEY
* `Reference` FOREIGN and REFERENCE
* `Repository` *Doesn't apply to SQL*
* `Unique` UNIQUE
* `Visible` VISIBLE|INVISIBLE

## Flush

```php
<?php
//! Хорошая скорость добавления записей в базу

use App\Entities\UserEntity;

$userEntity = new UserEntity();

// Добавляем 10 пользователей в commit
for($i = 0; $i < 10; $i++) {
    $userEntity->setUsername(sprintf('user#%s', $i));
    
    $entityManager->commit($userEntity);
}

// Добавляем пользователей в таблицу и очищаем commit
$entityManager->flush();
```

> Данная документация не подробная!