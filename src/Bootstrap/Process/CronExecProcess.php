<?php

namespace Swoft\Task\Bootstrap\Process;

use Swoft\App;
use Swoft\Bean\Annotation\BootProcess;
use Swoft\Bootstrap\Process\AbstractProcessInterface;
use Swoole\Process;

/**
 * Crontab process
 * @BootProcess("cronExec")
 */
class CronExecProcess extends AbstractProcessInterface
{
    /**
     * @param Process $process
     */
    public function run(Process $process)
    {
        $process->name($this->server->getPname() . ' cronexec process ');

        /** @var \Swoft\Task\Crontab\Crontab $cron */
        $cron = App::getBean('crontab');

        // Swoole/HttpServer
        $server = $this->server->getServer();

        $server->tick(0.5 * 1000, function () use ($cron) {
            $tasks = $cron->getExecTasks();
            if (! empty($tasks)) {
                foreach ($tasks as $task) {
                    // Diliver task
                    $this->task($task['taskClass'], $task['taskMethod']);
                    $cron->finishTask($task['key']);
                }
            }
        });
    }

    /**
     * Is it ready to start ?
     *
     * @return bool
     */
    public function isReady(): bool
    {
        $serverSetting = $this->server->getServerSetting();
        $cronable = (int)$serverSetting['cronable'];
        if ($cronable !== 1) {
            return false;
        }

        return true;
    }
}
