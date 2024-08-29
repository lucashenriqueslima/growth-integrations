<?php

namespace App\Domains;

class AuvoAccountDataEnvironment
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $apiToken,
        public readonly string $manager,
        public readonly int $idUserFrom,
    ) {}
}
