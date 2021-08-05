<?php

namespace Codememory\Components\Database\Orm\Commands;

use Codememory\Components\Database\Orm\Utils;
use Codememory\Components\Finder\Find;
use Codememory\Support\Str;

/**
 * Class Listing
 *
 * @package Codememory\Components\Database\Orm\Commands
 *
 * @author  Codememory
 */
class Listing
{

    /**
     * @var Utils
     */
    private Utils $utils;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $suffix;

    /**
     * @param Utils  $utils
     * @param string $path
     * @param string $suffix
     */
    public function __construct(Utils $utils, string $path, string $suffix)
    {

        $this->utils = $utils;
        $this->path = $path;
        $this->suffix = $suffix;

    }

    /**
     * @return array
     */
    public function getList(): array
    {

        $finder = new Find();

        $finder
            ->setPathForFind($this->path)
            ->file()
            ->byRegex(sprintf('%s\.php$', $this->suffix));
        $foundFiles = $finder->get();

        $files = [];

        foreach ($foundFiles as $foundFile) {
            $fullFileName = Str::trimAfterSymbol(Str::trimToSymbol($foundFile, '/', false), '.');
            $filename = Str::trimAfterSymbol($fullFileName, $this->suffix);

            $files[] = [
                'name'      => $filename,
                'full-name' => $fullFileName,
                'path'      => $foundFile
            ];
        }

        return $files;

    }

}