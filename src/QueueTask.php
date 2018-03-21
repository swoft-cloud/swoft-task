<?php

namespace Swoft\Task;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;
use Swoft\Task\Exception\TaskException;

/**
 * QueueTask
 *
 * @Bean()
 */
class QueueTask
{
    /**
     * @Value(env="${MESSAGE_QUEUE_KEY}")
     *
     * @var int
     */
    private $messageKey = 0x70001001;

    /**
     * @Value(env="${TASK_TMPDIR}")
     *
     * @var string
     */
    private $tmp = '/tmp/';

    /**
     * @var string
     */
    private $tmpFile = 'swoole.task';

    /**
     * @var mixed
     */
    private $queueId = null;

    /**
     * @param string $data
     * @param int    $taskId
     * @param int    $workerId
     *
     * @return bool
     */
    public function deliver(string $data, int $taskId = 0, int $workerId = 1)
    {
        $this->check();
        $data   = $this->pack($data, $taskId, $workerId);
        $result = \msg_send($this->queueId, $workerId, $data, false);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * @throws TaskException
     */
    private function check()
    {
        if (!function_exists('msg_get_queue')) {
            throw new TaskException('You must to compiled php with --enable-sysvmsg');
        }
        if($this->queueId === null){
            $this->queueId = \msg_get_queue((int)$this->messageKey);
        }

        if (empty($this->queueId)) {
            throw new TaskException(sprintf('msg_get_queue() failed. messageKey=%s', $this->messageKey));
        }
    }

    /**
     * @param string $data
     * @param string $taskId
     * @param string $workerId
     *
     * @return string
     */
    private function pack(string $data, string $taskId, string $workerId): string
    {
        $fromFd = 0;
        $type   = 7;
        if (!is_string($data)) {
            $data   = serialize($data);
            $fromFd |= 2;
        }
        if (strlen($data) >= 8180) {
            $tmpFile = tempnam($this->tmp, $this->tmpFile);
            file_put_contents($tmpFile, $data);
            $data   = pack('l', strlen($data)) . $tmpFile . "\0";
            $fromFd |= 1;
            $len    = 128 + 24;
        } else {
            $len = strlen($data);
        }

        return pack('lSsCCS', $taskId, $len, $workerId, $type, 0, $fromFd) . $data;
    }
}