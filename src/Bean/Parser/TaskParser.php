<?php

namespace Swoft\Task\Bean\Parser;

use Swoft\Bean\Annotation\Scope;
use Swoft\Bean\Collector;
use Swoft\Task\Bean\Annotation\Task;
use Swoft\Task\Collector\TaskCollector;

/**
 * task注解解析
 *
 * @uses      TaskParser
 * @version   2017年09月24日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class TaskParser extends AbstractParserInterface
{
    /**
     * task注解解析
     *
     * @param string $className
     * @param Task $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null $propertyValue
     * @return array
     */
    public function parser(string $className, $objectAnnotation = null, string $propertyName = "", string $methodName = "", $propertyValue = null)
    {
        $name = $objectAnnotation->getName();
        $beanName = empty($name) ? $className : $name;

        TaskCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);
        return [$beanName, Scope::SINGLETON, ""];
    }
}
