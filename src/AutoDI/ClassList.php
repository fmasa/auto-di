<?php

declare(strict_types=1);

namespace Fmasa\AutoDI;

use ReflectionClass;
use function array_diff;
use function array_filter;
use function array_keys;
use function array_values;
use function preg_grep;
use function preg_replace;
use function sprintf;

class ClassList
{
    /** @var string[] */
    private $classes;

    /**
     * @param string[] $classes
     */
    public function __construct(array $classes)
    {
        $this->classes = array_values($classes);
    }

    public function getMatching(string $classPattern) : ClassList
    {
        $classes = preg_grep($this->buildRegex($classPattern), $this->classes);

        return new ClassList($classes);
    }

    private function buildRegex(string $classPattern) : string
    {
        $replacements = [
            '~\\*\\*~' => '(.*)', // ** for n-level wildcard
            '~(\\\\)~' => '\\\\\\\\', // \ as NS delimiter
            '~(?<!\.)\*~' => '\w+', // * for single NS level / class name wildcard
            '~\{((\w+,?)+)\}~' => '($1)',
            '~,~' => '|', // PHP 7-like group use
        ];

        $regex = preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $classPattern
        );

        return sprintf('~^%s$~', $regex);
    }

    public function getClasses() : ClassList
    {
        $classes = array_filter($this->classes, static function ($c) {
            $reflection = new ReflectionClass($c);

            return ! $reflection->isTrait() && ! $reflection->isInterface() && ! $reflection->isAbstract();
        });

        return new ClassList($classes);
    }

    public function getInterfaces() : ClassList
    {
        $interfaces = array_filter($this->classes, static function ($c) {
            return (new ReflectionClass($c))->isInterface();
        });

        return new ClassList($interfaces);
    }

    public function getWithoutClasses(ClassList $list) : ClassList
    {
        return new ClassList(array_diff($this->classes, $list->classes));
    }

    /**
     * @return string[]
     */
    public function toArray() : array
    {
        return $this->classes;
    }
}
