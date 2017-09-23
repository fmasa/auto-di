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
        $this->classes = array_values($classes);
    }

    /**
     * @param string $classPattern
     * @return ClassList
     */
    public function getMatching($classPattern)
    {
        $classes = preg_grep($this->buildRegex($classPattern), $this->classes);

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
            '~\{((\w+,?)+)\}~' => '($1)',
            '~,~' => '|', // PHP 7-like group use
        ];

        $regex = preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $classPattern
        );

        return "~^$regex$~";
    }

    /**
     * @return ClassList
     */
    public function getClasses()
    {
        $classes = array_filter($this->classes, function ($c) {
            $reflection = new \ReflectionClass($c);
            return ! $reflection->isTrait() && ! $reflection->isInterface();
        });

        return new ClassList($classes);
    }

    /**
     * @return ClassList
     */
    public function getInterfaces()
    {
        $interfaces = array_filter($this->classes, function ($c) {
            return (new \ReflectionClass($c))->isInterface();
        });

        return new ClassList($interfaces);
    }

    /**
     * @return ClassList
     */
    public function getWithoutClasses(ClassList $list)
    {
        return new ClassList(array_diff($this->classes, $list->classes));
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return $this->classes;
    }

}
