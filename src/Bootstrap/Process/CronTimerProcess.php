<?php

namespace Swoft\Task\Bootstrap\Process;

use Swoft\App;
use Swoft\Bean\Annotation\BootProcess;
use Swoft\Bootstrap\Process\AbstractProcessInterface;
use Swoole\Process;

/**
 * Crontab timer process
 * @BootProcess("cronTimer")
 */
class CronTimerProcess extends AbstractProcessInterface
{
    /**
     * @param Process $process
     */
    public function run(Process $process)
    {
        $process->name($this->server->getPname() . ' crontimer process ');
        $cron = App::getBean('crontab');

        // Swoole/HttpServer
        $server = $this->server->getServer();

        $time = (60 - date('s')) * 1000;
        $server->after($time, function () use ($server, $cron) {
            // Every minute check all tasks, and prepare the tasks that next execution point needs
            $cron->checkTask();
            $server->tick(60 * 1000, function () use ($cron) {
                $cron->checkTask();
            });
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
