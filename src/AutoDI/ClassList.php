<?php

namespace Fmasa\AutoDI;

class ClassList
{

    /** @var string[] */
    private $classes;

    /**
     * @param string[] $classes
     */
    public function __construct(array $classes)
    {
        $this->classes = $classes;
    }

    /**
     * @param string $classPattern
     * @return ClassList
     */
    public function getMatching($classPattern)
    {
        $classes = preg_grep($this->buildRegex($classPattern), $this->classes);
        $classes = array_filter($classes, function ($c) { return ! (new \ReflectionClass($c))->isTrait(); });

        return new ClassList($classes);
    }

    /**
     * @param string $classPattern
     * @return string
     */
    private function buildRegex($classPattern)
    {
        $replacements = [
            '~\\*\\*~' => '(.*)', // ** for n-level wildcard
            '~(\\\\)~' => '\\\\\\\\', // \ as NS delimiter
            '~(?<!\.)\*~' => '\w+', // * for single NS level / class name wildcard
        ];

        $regex = preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $classPattern
        );

        return "~^$regex$~";
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return array_values($this->classes);
    }

}
