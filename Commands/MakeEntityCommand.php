<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Console\Command;
use Codememory\Components\Database\Orm\Utils;
use Codememory\Components\Database\Schema\Interfaces\ColumnTypeInterface;
use Codememory\FileSystem\File;
use Codememory\FileSystem\Interfaces\FileInterface;
use Codememory\Support\Str;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeEntityCommand
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class MakeEntityCommand extends Command
{

    private const METHOD_SET = 'Setter';
    private const METHOD_GET = 'Getter';
    private const METHOD_GET_SET = 'Setter & Getter';

    /**
     * @inheritDoc
     */
    protected ?string $command = 'make:entity';

    /**
     * @inheritDoc
     */
    protected ?string $description = 'Create entity';

    /**
     * @var array
     */
    private array $columns = [];

    /**
     * @inheritDoc
     */
    protected function wrapArgsAndOptions(): Command
    {

        $this->addArgument('entity-name', InputArgument::REQUIRED, 'Entity name without suffix');

        return $this;

    }

    /**
     * @inheritDoc
     */
    protected function handler(InputInterface $input, OutputInterface $output): int
    {

        $filesystem = new File();
        $utils = new Utils();
        $entityName = $input->getArgument('entity-name');
        $fullEntityPath = sprintf('%s%s%s.php', $utils->getPathWithEntities(), $entityName, $utils->getEntitySuffix());
        $fullRepositoryPath = sprintf('%s%s%s.php', $utils->getPathWithRepositories(), $entityName, $utils->getRepositorySuffix());

        if ($filesystem->exist($fullEntityPath)) {
            $question = 'The entity %s already exists. Do you want to recreate';

            $reCreate = $this->io->confirm(sprintf($question, $this->tags->yellowText($entityName)), false);

            if (!$reCreate) {
                $this->io->warning('Entity creation canceled');

                return Command::INVALID;
            }
        }

        $tableName = $this->askTableName();
        $createRepository = $this->askCreateRepository($entityName);

        $this->askCreateColumn();

        [$propertiesToString, $methodsToString] = $this->buildColumns();

        $entityAttributes = $this->getEntityAttributes($tableName, $createRepository, $entityName, $utils);
        $entityStub = $this->buildEntity($entityAttributes, $utils, $entityName, $propertiesToString, $methodsToString, $this->getEntityStub($filesystem));

        file_put_contents($filesystem->getRealPath($fullEntityPath), $entityStub);

        $this->io->success([
            sprintf('The entity %s has been successfully created', $entityName),
            sprintf('Path: %s', $fullEntityPath)
        ]);

        if ($createRepository) {
            $repositoryStub = $this->buildRepository($this->getRepositoryStub($filesystem), $entityName, $utils);

            file_put_contents($filesystem->getRealPath($fullRepositoryPath), $repositoryStub);

            $this->io->success([
                sprintf('Repository for entity %s has been successfully created', $entityName),
                sprintf('Path: %s', $fullRepositoryPath)
            ]);
        }

        return Command::SUCCESS;

    }

    /**
     * @param array $tags
     * @param array $values
     *
     * @return string
     */
    private function generatePHPDoc(array $tags, array $values): string
    {

        $tagsWithValue = [];

        foreach ($tags as $index => $tag) {
            $value = array_key_exists($index, $values) ? $values[$index] : null;

            $tagsWithValue[] = sprintf(' * @%s %s', $tag, $value);
        }

        $docSchema = <<<DOC
        /**
        %s
         */
        DOC;
        $doc = sprintf($docSchema, implode("\n", $tagsWithValue));
        $docToArray = array_map(function (string $value) {
            return sprintf("\t%s", $value);
        }, explode(PHP_EOL, $doc));

        return implode(PHP_EOL, $docToArray);

    }

    /**
     * @param string $attribute
     * @param array  $arguments
     *
     * @return string
     */
    private function generateAttribute(string $attribute, array $arguments = []): string
    {

        $arrayOfArgumentsAsString = [];

        foreach ($arguments as $name => $value) {
            if (is_string($value)) {
                $value = sprintf('\'%s\'', $value);
            } else if (is_bool($value)) {
                $value = true === $value ? 'true' : 'false';
            } else if (is_null($value)) {
                $value = 'null';
            }

            $arrayOfArgumentsAsString[] = sprintf('%s: %s', $name, $value);
        }

        $argumentsToString = implode(', ', $arrayOfArgumentsAsString);
        $attributeSchema = <<<ATTR
            #[%s%s]
        ATTR;

        return sprintf($attributeSchema, $attribute, empty($argumentsToString) ? null : sprintf('(%s)', $argumentsToString));

    }

    /**
     * @param string $propertyName
     * @param string $type
     * @param bool   $isPublic
     * @param array  $attributes
     *
     * @return string
     */
    private function generateProperty(string $propertyName, string $type, bool $isPublic, array $attributes = []): string
    {

        $attributesToString = implode(PHP_EOL, $attributes);
        $modifier = $isPublic ? 'public' : 'private';
        $PHPDoc = sprintf("%s\n", $this->generatePHPDoc(['var'], [sprintf('%s', $type)]));

        if (!empty($attributesToString)) {
            $attributesToString = sprintf("%s\n", $attributesToString);
        }

        $propertySchema = <<<PROPRTY
            %s %s $%s = null;
        PROPRTY;
        $propertySchema = sprintf($propertySchema, $modifier, $type, $propertyName);

        return $PHPDoc . $attributesToString . $propertySchema;

    }

    /**
     * @param string      $methodName
     * @param bool        $isPublic
     * @param string      $returnType
     * @param array       $arguments
     * @param string|null $body
     *
     * @return string
     */
    private function generateMethod(string $methodName, bool $isPublic, string $returnType, array $arguments = [], ?string $body = null): string
    {

        $modifier = $isPublic ? 'public' : 'private';
        $arrayOfArgumentsAsString = [];
        $PHPDocData = [
            'tags'   => [],
            'values' => []
        ];

        foreach ($arguments as $type => $variable) {
            $type = is_numeric($type) ? 'mixed' : $type;
            $variable = sprintf('$%s', $variable);

            $arrayOfArgumentsAsString[] = sprintf('%s %s', $type, $variable);
            $PHPDocData['tags'][] = 'param';
            $PHPDocData['values'][] = sprintf('%s %s', $type, $variable);
        }

        $PHPDocData['tags'][] = 'return';
        $PHPDocData['values'][] = $returnType;
        $PHPDoc = $this->generatePHPDoc(...$PHPDocData);
        $PHPDoc = !empty($PHPDoc) ? sprintf("%s\n", $PHPDoc) : null;

        $argumentsToString = implode(', ', $arrayOfArgumentsAsString);
        $methodSchema = <<<METHOD
            %s function %s(%s): %s
            {
            
        %s
            
            }
        METHOD;

        $bodyToArray = array_map(function (string $value) {
            return sprintf("\t\t%s", $value);
        }, explode(PHP_EOL, $body));
        $body = implode(PHP_EOL, $bodyToArray);

        $method = sprintf($methodSchema, $modifier, $methodName, $argumentsToString, $returnType, $body);

        return $PHPDoc . $method;

    }

    /**
     * @return string
     */
    private function askTableName(): string
    {

        return $this->io->ask('Specify the name of the table', null, function (mixed $tableName) {
            if (null === $tableName) {
                throw new RuntimeException('The table name must be of type string');
            }

            return $tableName;
        });

    }

    /**
     * @param string $entityName
     *
     * @return bool
     */
    private function askCreateRepository(string $entityName): bool
    {

        return $this->io->confirm(sprintf('Create repository for entity %s', $this->tags->yellowText($entityName)), true);

    }

    /**
     * @param mixed    $askValue
     * @param string   $title
     * @param array    $values
     * @param callable $handler
     *
     * @return mixed
     */
    private function createHelpForAsk(mixed $askValue, string $title, array $values, callable $handler): mixed
    {

        if ('--help' === $askValue) {
            $values = array_map(function (string $value) {
                return sprintf('%s %s', $this->tags->colorText('-', '#ffffff'), $this->tags->yellowText($value));
            }, $values);

            $this->io->text($this->tags->blueText(sprintf('%s:', $title)));

            foreach ($values as $value) {
                $this->io->text(sprintf('%s%s', Str::repeat(' ', 2), $value));
            }

            return call_user_func($handler);
        }

        return $askValue;

    }

    /**
     * @return void
     */
    private function askCreateColumn(): void
    {

        $this->io->ask('Specify a column name', null, function (mixed $columnName) {

            if (null === $columnName) {
                return;
            }

            if (!preg_match('/[a-zA-Z_]+/', $columnName)) {
                throw new RuntimeException('Column name must match the given regular expression [a-zA-Z_]+');
            }

            $columnType = $this->askColumnType();
            $length = $this->askColumnLength();
            $nullable = $this->askNullable();
            $default = $this->askDefault();
            $methods = $this->chooseMethods();

            $this->columns[] = [
                'name'     => $columnName,
                'type'     => $columnType,
                'length'   => $length,
                'nullable' => $nullable,
                'default'  => $default,
                'method'   => $methods
            ];

            $this->askCreateColumn();
        });

    }

    /**
     * @return string
     */
    private function askColumnType(): string
    {

        $question = sprintf('Specify the column type or type %s for a list of all types', $this->tags->yellowText('--help'));
        $typeNames = [];

        $reflectionColumnType = new ReflectionClass(ColumnTypeInterface::class);

        foreach ($reflectionColumnType->getMethods() as $reflectionMethod) {
            $typeNames[] = $reflectionMethod->getName();
        }

        return $this->io->askWithAutocomplete($question, $typeNames, null, function (mixed $columnType) use ($typeNames) {
            if (null === $columnType) {
                throw new RuntimeException('Specify the column type');
            }

            $columnType = $this->createHelpForAsk($columnType, 'List of available types', $typeNames, function () {
                return $this->askColumnType();
            });

            if (!in_array($columnType, $typeNames)) {
                throw new RuntimeException(sprintf('The type %s does not exist write --help for all types of spokes', $columnType));
            }

            return $columnType;
        });

    }

    /**
     * @return int|null
     */
    private function askColumnLength(): ?int
    {

        return $this->io->ask('Enter the length of the column value, or just hit enter', null, function (mixed $length) {
            if (null === $length) {
                return null;
            } else if (!preg_match('/[0-9]+/', $length)) {
                throw new RuntimeException('Length must be of type int');
            }

            return (int) $length;
        });

    }

    /**
     * @return bool
     */
    private function askNullable(): bool
    {

        return $this->io->confirm('Allow null for column', true);

    }

    /**
     * @return string|null
     */
    private function askDefault(): ?string
    {

        return $this->io->ask('Default value or enter to cancel');

    }

    /**
     * @return string
     */
    private function chooseMethods(): string
    {

        return $this->io->choice('Choose methods for the column', [
            self::METHOD_SET,
            self::METHOD_GET,
            self::METHOD_GET_SET
        ], 2);

    }

    /**
     * @param FileInterface $filesystem
     *
     * @return string
     */
    private function getEntityStub(FileInterface $filesystem): string
    {

        return file_get_contents($filesystem->getRealPath('./Commands/Stubs/EntityStub.stub'));

    }

    /**
     * @param FileInterface $filesystem
     *
     * @return string
     */
    private function getRepositoryStub(FileInterface $filesystem): string
    {

        return file_get_contents($filesystem->getRealPath('./Commands/Stubs/RepositoryStub.stub'));

    }

    /**
     * @return array
     */
    private function buildColumns(): array
    {

        $properties = [];
        $methods = [];

        foreach ($this->columns as $column) {
            $attributes = $this->buildAttributesForColumn($column);

            $properties[] = $this->generateProperty($column['name'], 'mixed', false, $attributes);

            if (self::METHOD_SET === $column['method']) {
                $methods[] = $this->generateSetterForColumn($column);
            } else if (self::METHOD_GET === $column['method']) {
                $methods[] = $this->generateGetterForColumn($column);
            } else if (self::METHOD_GET_SET === $column['method']) {
                $methods[] = $this->generateSetterForColumn($column);
                $methods[] = $this->generateGetterForColumn($column);
            }
        }

        return [
            implode(PHP_EOL . PHP_EOL, $properties),
            implode(PHP_EOL . PHP_EOL, $methods)
        ];

    }

    /**
     * @param array $column
     *
     * @return array
     */
    private function buildAttributesForColumn(array $column): array
    {

        $attributes = [];

        $attributes[] = $this->generateAttribute('ORM\Column', [
            'name'     => $column['name'],
            'type'     => $column['type'],
            'length'   => $column['length'],
            'nullable' => $column['nullable']
        ]);

        if ('id' === $column['name']) {
            $attributes[] = $this->generateAttribute('ORM\Identifier');
        }

        if (null !== $column['default']) {
            $attributes[] = $this->generateAttribute('ORM\DefaultValue', [
                'value' => $column['value']
            ]);
        }

        return $attributes;

    }

    /**
     * @param array $column
     *
     * @return string
     */
    private function generateSetterForColumn(array $column): string
    {

        return $this->generateMethod(
            sprintf('set%s', ucfirst($column['name'])),
            true,
            'static',
            ['mixed' => 'value'],
            <<<BODY
            \$this->{$column['name']} = \$value;
            
            return \$this;
            BODY
        );

    }

    /**
     * @param array $column
     *
     * @return string
     */
    private function generateGetterForColumn(array $column): string
    {

        return $this->generateMethod(
            sprintf('get%s', ucfirst($column['name'])),
            true,
            'mixed',
            [],
            <<<BODY
                return \$this->{$column['name']};
                BODY
        );

    }

    /**
     * @param string $tableName
     * @param bool   $creatingRepository
     * @param string $entityName
     * @param Utils  $utils
     *
     * @return array
     */
    private function getEntityAttributes(string $tableName, bool $creatingRepository, string $entityName, Utils $utils): array
    {

        $entityAttributes = [];

        $entityAttributes[] = $this->generateAttribute('ORM\Entity', ['tableName' => $tableName]);

        if ($creatingRepository) {
            $entityAttributes[] = $this->generateAttribute('ORM\Repository', [
                'repository' => sprintf('%s%s%s', $utils->getRepositoryNamespace(), $entityName, $utils->getRepositorySuffix())
            ]);
        }

        return array_map(function (string $attribute) {
            return trim($attribute);
        }, $entityAttributes);

    }

    /**
     * @param array  $entityAttributes
     * @param Utils  $utils
     * @param string $entityName
     * @param string $propertiesToString
     * @param string $methodsToString
     * @param string $entityStub
     *
     * @return string
     */
    private function buildEntity(array $entityAttributes, Utils $utils, string $entityName, string $propertiesToString, string $methodsToString, string $entityStub): string
    {

        return str_replace([
            '{entityAttributes}',
            '{namespace}',
            '{entityName}',
            '{entitySuffix}',
            '{entityBody}'
        ], [
            implode(PHP_EOL, $entityAttributes),
            Str::trimAfterSymbol($utils->getEntityNamespace(), '\\', false),
            $entityName,
            $utils->getEntitySuffix(),
            sprintf("%s\n\n%s", $propertiesToString, $methodsToString)
        ], $entityStub);

    }

    /**
     * @param string $repositoryStub
     * @param string $repositoryName
     * @param Utils  $utils
     *
     * @return string
     */
    private function buildRepository(string $repositoryStub, string $repositoryName, Utils $utils): string
    {

        return str_replace([
            '{namespace}',
            '{repositoryName}',
            '{repositorySuffix}'
        ], [
            Str::trimAfterSymbol($utils->getRepositoryNamespace(), '\\', false),
            $repositoryName,
            $utils->getRepositorySuffix()
        ], $repositoryStub);

    }

}