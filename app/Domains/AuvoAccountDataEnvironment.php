<?php

namespace App\Domains;

class AuvoAccountDataEnvironment
{
    public function __construct(
        public string $apiKey,
        public string $apiToken,
        public string $manager,
        public int $idUserFrom,
    ) {}
}
