<?php

declare(strict_types=1);

interface CacheDirectoryAwareInterface
{
	public function setCachedir(?string $dir = null): void;
	public function getCachedir(): ?string;
}
