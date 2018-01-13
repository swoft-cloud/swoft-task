<?php

namespace Swoft\Task\Bootstrap\Listeners;

use Swoft\App;
use Swoft\Bean\Annotation\BeforeStart;
use Swoft\Bootstrap\Listeners\Interfaces\BeforeStartInterface;
use Swoft\Bootstrap\Server\AbstractServer;
use Swoft\Task\Crontab\TableCrontab;

/**
 * the listener of before start
 *
 * @BeforeStart()
 * @uses      BeforeStartListener
 * @version   2018年01月12日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class BeforeStartListener implements BeforeStartInterface
{
    /**
     * @param AbstractServer $server
     */
    public function onBeforeStart(AbstractServer &$server)
    {
        /** @var array[] $settings */
        $settings = App::getAppProperties()->get('server');
        $settings = $settings['server'];
        // 初始化定时任务共享内存表
        if (isset($settings['cronable']) && (int)$settings['cronable'] === 1) {
            $this->initCrontabMemoryTable();
        }
    }

    /**
     * init table of crontab
     */
    private function initCrontabMemoryTable()
    {
        /** @var array[] $settings */
        $settings = App::getAppProperties()->get('server');
        $settings = $settings['server'];

        $taskCount = isset($settings['task_count']) && $settings['task_count'] > 0 ? $settings['task_count'] : null;
        $taskQueue = isset($settings['task_queue']) && $settings['task_queue'] > 0 ? $settings['task_queue'] : null;

        TableCrontab::init($taskCount, $taskQueue);
    }
}