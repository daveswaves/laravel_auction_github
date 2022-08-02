<?php
// app/Logging/QueryLogger.php

/**
 * Created this file and the 'Logging' directory
 */
namespace App\Logging;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class QueryLogger
{
    protected $debugId;
    protected $logger;

    /**
     * Create an instance of the QueryLogger class
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     *
     * @throws \Ramsey\Uuid\Exception\UnsatisfiedDependencyException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->debugId = Uuid::uuid4()->toString();
    }

    public function __call($name, $arguments)
    {
        $this->logger->{$name}('['.$this->debugId.'] ' .$arguments[0], ...array_slice($arguments, 1));
    }
}