<?php

namespace App\Contracts;

use GuzzleHttp\Client;

interface ClientServiceContract
{
    public function __construct(string $baseUrl);
    public function getClient(): Client;
}
