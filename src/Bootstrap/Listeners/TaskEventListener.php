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
 * The listener of swoole task
 * @SwooleListener({
 *     SwooleEvent::ON_TASK,
 *     SwooleEvent::ON_FINISH,
 *     SwooleEvent::ON_PIPE_MESSAGE
 * })
 */
class TaskEventListener implements TaskInterface, PipeMessageInterface, FinishInterface
{
    public function onFinish(Server $server, int $taskId, string $data)
    {
        var_dump($data);
    }

    /**
     * @param \Swoole\Server $server
     * @param int            $srcWorkerId
     * @param string         $message
     * @return void
     */
    public function onPipeMessage(Server $server, int $srcWorkerId, string $message)
    {
        list($type, $data) = PipeMessage::unpack($message);
        if ($type === PipeMessage::TYPE_TASK) {
            $this->onPipeMessageTask($data);
        }
    }

    /**
     * @param \Swoole\Server $server
     * @param int            $taskId
     * @param int            $workerId
     * @param mixed          $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function onTask(Server $server, int $taskId, int $workerId, $data)
    {
        Task::setId($taskId);

        $task = @unserialize($data);

        $name = $task['name'];
        $type = $task['type'];
        $method = $task['method'];
        $params = $task['params'];
        $logid = $task['logid'] ?? uniqid('', true);
        $spanid = $task['spanid'] ?? 0;

        $event = new BeforeTaskEvent(TaskEvent::BEFORE_TASK, $logid, $spanid, $name, $method, $type);
        App::trigger($event);
        $result = Task::run($name, $method, $params);
        App::trigger(TaskEvent::AFTER_TASK, null, $type);

        if ($type === Task::TYPE_CRON) {
            return $result;
        }
        $server->finish($result);
    }

    /**
     * Pipe message on task
     *
     * @param array $data
     */
    private function onPipeMessageTask(array $data)
    {
        // Task info
        $type = $data['type'];
        $taskName = $data['name'];
        $params = $data['params'];
        $timeout = $data['timeout'];
        $methodName = $data['method'];

        // delever task
        Task::deliver($taskName, $methodName, $params, $type, $timeout);
    }
}