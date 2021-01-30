<?php

namespace Plasticode\Traits;

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

    private function log(string $message, ?array $data = null): void
    {
        if (is_null($this->logger)) {
            return;
        }

        $this->logger->info($message, $data ?? []);
    }
}
