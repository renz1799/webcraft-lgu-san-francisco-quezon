<?php

namespace App\Core\Services\Contracts\GoogleDrive;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;

interface GoogleDriveClientFactoryInterface
{
    public function makeClient(): GoogleClient;

    public function makeAuthorizedClient(): GoogleClient;

    public function makeAuthorizedDrive(): GoogleDrive;
}
