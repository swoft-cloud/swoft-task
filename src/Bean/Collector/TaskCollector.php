<?php

namespace Swoft\Task\Bean\Collector;

use Swoft\Bean\CollectorInterface;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Task annotation collector
 */
class TaskCollector implements CollectorInterface
{

    /**
     * @var array
     */
    private static $tasks = [];

    /**
     * @param string $className
     * @param object $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null   $propertyValue
     * @return void
     */
    public static function collect(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    ) {
        if ($objectAnnotation instanceof Task) {
            self::collectTask($className, $objectAnnotation);

            return;
        }

        if ($objectAnnotation instanceof Scheduled) {
            self::collectScheduled($className, $objectAnnotation, $methodName);

            return;
        }
    }

    /**
     * collect the annotation of task
     *
     * @param string $className
     * @param Task   $objectAnnotation
     */
    private static function collectTask(string $className, Task $objectAnnotation)
    {
        $name = $objectAnnotation->getName();
        $beanName = empty($name) ? $className : $name;
        $coroutine = $objectAnnotation->isCoroutine();

        self::$tasks[$beanName]['task'] = [
            $className,
            $coroutine
        ];
    }

    /**
     * collect the annotation of Scheduled
     *
     * @param string    $className
     * @param Scheduled $objectAnnotation
     * @param string    $methodName
     */
    private static function collectScheduled(string $className, Scheduled $objectAnnotation, string $methodName)
    {
        $cron = $objectAnnotation->getCron();
        $taskName = self::$tasks[$className]['task'];

        $task = [
            'cron'   => $cron,
            'task'   => $taskName,
            'method' => $methodName,
            'type'   => \Swoft\Task\Task::TYPE_CRON,
        ];

        self::$tasks[$className]['crons'][] = $task;
    }

    /**
     * @return array
     */
    public static function getCollector(): array
    {
        return self::$tasks;
    }

}