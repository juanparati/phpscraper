<?php

use Amp\LazyPromise;
use function Amp\Promise\wait;
use function Amp\delay;

/**
 * Class ThreadThrottle.
 *
 * Helper that pauses the execution thread according to X operations per second.
 */
class ThreadThrottle
{

    /**
     * Maximum number of operations per second.
     *
     * @var int
     */
    protected $maxOperations;


    /**
     * Time interval in seconds.
     *
     * @var float
     */
    protected $timeInterval;


    /**
     * Time wait in ms.
     * 
     * @var int 
     */
    protected $timeWait = 1000;


    /**
     * Current number of operations.
     *
     * @var int
     */
    protected $currentOperations = 0;


    /**
     * Last operation time as microtime.
     *
     * @var float
     */
    protected $lastOperationTime = 0;


    /**
     * ThreadThrottle constructor.
     *
     * @param int $maxOperations
     * @param int $timeInterval
     * @param int $timeWait
     */
    public function __construct(
        int $maxOperations, 
        int $timeInterval = 1000,
        int $timeWait     = 1000
    )
    {
        $this->maxOperations     = $maxOperations;
        $this->timeInterval      = $timeInterval / 1000;
        $this->timeWait          = $timeWait;
        $this->lastOperationTime = microtime(true);
    }


    /**
     * Add new operations to the counter.
     *
     * If the counter reaches the maximum number of operations then it will
     * wait until the defined waiting time.
     *
     * @param int $ops
     * @return \Amp\Delayed|\Amp\Promise
     * @throws Throwable
     */
    public function addOperations(int $ops = 1)
    {
        $time = microtime(true);

        if (($this->lastOperationTime + $this->timeInterval) < $time)
        {
            $this->currentOperations = 0;
            $this->lastOperationTime = $time;
        }
        
        $this->currentOperations += $ops;

        if ($this->currentOperations > $this->maxOperations)
        {
            $this->currentOperations = 0;
            $this->lastOperationTime = microtime(true) + ($this->timeWait / 1000);

            return delay($this->timeWait);
        }
        else
            return delay(0);
    }

    /**
     * It is the asynchronous version of sleep.
     *
     * @param int $ms
     * @throws \Throwable
     */
    public static function asyncWait(int $ms)
    {
        wait(\Amp\Promise\timeout(
            new LazyPromise(function() {
                // Do nothing, just asynchronous wait
            }),
            $ms
        ));
    }

}