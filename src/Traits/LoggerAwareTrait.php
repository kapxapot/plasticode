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

    private function hasLogger(): bool
    {
        return $this->logger !== null;
    }

    protected function log(string $message, ?array $data = null): void
    {
        if (!$this->hasLogger()) {
            return;
        }

        $this->logger->info($message, $data ?? []);
    }

    protected function logEx(Exception $ex): void
    {
        $this->log(
            sprintf(
                '%s %s',
                $ex->getMessage(),
                json_encode(Debug::exceptionTrace($ex))
            )
        );
    }
}
