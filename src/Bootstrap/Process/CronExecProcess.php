<?php

namespace Swoft\Task\Bootstrap\Process;

use Swoft\App;
use Swoft\Process\Process as SwoftProcess;
use Swoft\Process\ProcessInterface;

/**
 * Crontab process
 */
class CronExecProcess implements ProcessInterface
{
    public function run(SwoftProcess $process)
    {
        $process->name(App::$server->getPname() . ' cronexec process ');

        /** @var \Swoft\Task\Crontab\Crontab $cron */
        $cron = App::getBean('crontab');

        // Swoole/HttpServer
        $server = App::$server->getServer();

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
