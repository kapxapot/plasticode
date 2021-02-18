<?php

namespace Plasticode\Traits;

use Exception;
use Plasticode\Util\Debug;
use Psr\Log\LoggerInterface;

trait LoggerAwareTrait
{
    private ?LoggerInterface $logger = null;

    /**
     * @return $this
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    protected function log(string $message, ?array $data = null): void
    {
        if (is_null($this->logger)) {
            return;
        }

        $this->logger->info($message, $data ?? []);
    }

    protected function logEx(Exception $ex): void
    {
        $this->log(
            json_encode(
                Debug::exceptionTrace($ex)
            )
        );
    }
}
