<?php

namespace Swoft\Task\Bean\Parser;

use Swoft\Bean\Collector;
use Swoft\Bean\Parser\AbstractParserInterface;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Collector\TaskCollector;

/**
 * ScheduledParser注解解析
 *
 * @uses      ScheduledParser
 * @version   2017年09月24日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class ScheduledParser extends AbstractParserInterface
{
    /**
     * ScheduledParser注解解析
     *
     * @param string $className
     * @param Scheduled $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null $propertyValue
     * @return mixed
     */
    public function parser(string $className, $objectAnnotation = null, string $propertyName = "", string $methodName = "", $propertyValue = null)
    {
        $collector = TaskCollector::getCollector();
        if (!isset($collector[$className])) {
            return;
        }

        TaskCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);
    }
}
