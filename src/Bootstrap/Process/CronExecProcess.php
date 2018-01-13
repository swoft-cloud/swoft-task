<?php

namespace Swoft\Task\Bootstrap\Process;

use Swoft\App;
use Swoft\Bean\Annotation\BootProcess;
use Swoft\Bootstrap\Process\AbstractProcessInterface;
use Swoole\Process;

/**
 * Crontab执行进程
 *
 * @BootProcess("cronExec")
 * @uses      CronExecProcess
 * @version   2017年10月22日
 * @author    caiwh <471113744@qq.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class CronExecProcess extends AbstractProcessInterface
{
    /**
     * @param Process $process
     */
    public function run(Process $process)
    {
        $process->name($this->server->getPname() . " cronexec process ");
        $cron = App::getBean('crontab');

        // Swoole/HttpServer
        $server = $this->server->getServer();

        $server->tick(0.5 * 1000, function () use ($cron) {
            $tasks = $cron->getExecTasks();
            if (!empty($tasks)) {
                foreach ($tasks as $task) {
                    // 投递任务
                    $this->task($task['taskClass'], $task['taskMethod']);
                    $cron->finishTask($task['key']);
                }
            }
        });
    }

    /**
     * 进程启动准备工作
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
