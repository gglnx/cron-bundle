<?php

declare(strict_types=1);

namespace Shapecode\Bundle\CronBundle\Service;

use ReflectionClass;

class AttributeReader
{
    /**
     * @param ReflectionClass $class
     * @param class-string<T> $attributeName
     * @return list<T>
     */
    public function getClassAttributes(ReflectionClass $class, string $attributeName): array
    {
        $attribs = [];
        foreach ($class->getAttributes($attributeName) as $attribute) {
            $attribs[] = $attribute->newInstance();
        }

        return $attribs;
    }
}
