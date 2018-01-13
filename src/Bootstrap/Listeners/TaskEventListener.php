<?php

namespace Swoft\Task\Bootstrap\Listeners;

use Swoft\App;
use Swoft\Bean\Annotation\SwooleListener;
use Swoft\Bootstrap\Listeners\Interfaces\FinishInterface;
use Swoft\Bootstrap\Listeners\Interfaces\PipeMessageInterface;
use Swoft\Bootstrap\Listeners\Interfaces\TaskInterface;
use Swoft\Bootstrap\Server\PipeMessage;
use Swoft\Task\Event\Events\BeforeTaskEvent;
use Swoft\Task\Event\TaskEvent;
use Swoft\Task\Task;
use Swoole\Server;
use Swoft\Bootstrap\SwooleEvent;

/**
 * the listener of swoole task
 *
 * @SwooleListener({
 *     SwooleEvent::ON_TASK,
 *     SwooleEvent::ON_FINISH,
 *     SwooleEvent::ON_PIPE_MESSAGE
 * })
 * @uses      TaskEventListener
 * @version   2018年01月13日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class TaskEventListener implements TaskInterface,PipeMessageInterface,FinishInterface
{
    public function onFinish(Server $server, int $taskId, string $data)
    {
        var_dump($data);
    }

    public function onPipeMessage(Server $server, int $srcWorkerId, string $message)
    {
        list($type, $data) = PipeMessage::unpack($message);
        if ($type == PipeMessage::TYPE_TASK) {
            $this->onPipeMessageTask($data);
        }
    }

    public function onTask(Server $server, int $taskId, int $workerId, $data)
    {
        Task::setId($taskId);

        $task = @unserialize($data);

        $name = $task['name'];
        $type = $task['type'];
        $method = $task['method'];
        $params = $task['params'];
        $logid = $task['logid'] ?? uniqid();
        $spanid = $task['spanid'] ?? 0;

        $event = new BeforeTaskEvent(TaskEvent::BEFORE_TASK, $logid, $spanid, $name, $method, $type);
        App::trigger($event);
        $result = Task::run($name, $method, $params);
        App::trigger(TaskEvent::AFTER_TASK, null, $type);

        if ($type == Task::TYPE_CRON) {
            return $result;
        }
        $server->finish($result);
    }

    /**
     * 任务类型的管道消息
     *
     * @param array $data 数据
     */
    private function onPipeMessageTask(array $data)
    {
        // 任务信息
        $type = $data['type'];
        $taskName = $data['name'];
        $params = $data['params'];
        $timeout = $data['timeout'];
        $methodName = $data['method'];

        // 投递任务
        Task::deliver($taskName, $methodName, $params, $type, $timeout);
    }
}