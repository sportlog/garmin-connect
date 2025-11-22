# Garmin-connect

A PHP (>= PHP 8.2) library to connect to the Garmin API. This is a port of [garth](https://github.com/matin/garth/).

## Install via Composer

You can install sportlog/garmin-connect using Composer.

```bash
$ composer require sportlog/garmin-connect
```

## Main functions

### GarminConnect

- GarminConnect::login(...): Login to garmin connect API. If MFA is enabled, you need a subsequent call to GarminConnect::resumeLogin(...).
- GarminConnect::resumeLogin(...): In case MFA is enabled, you must resume login by providing the received MFA-code.
- GarminConnect::connectApi(...): Try to connect with previously fetched token. This refreshes an invalid token, if possible. If false is returned, a call to GarminConnect::login(...) is required. This function is a convenient function to not require passing the password all the time in case a valid token exists.

### GarminConnectApi

- GarminConnectApi::runQuery(...): Run query against Garmin connect Api. You can use the factories for getting predefined queries or pass a custom query.

The tokens are saved to the filesystem in folder "./storage" of the current directory. However you can provide your own implementation of TokenStorageInterface, if you want to save the tokens to database, for instance.

## How to use

```php
<?php

require 'vendor/autoload.php';

use Sportlog\GarminConnect\ConnectStatus;
use Sportlog\GarminConnect\GarminConnect;
use Sportlog\GarminConnect\Queries\GarminConnectQueryFactory;

// You can optionally provide your own implementation of TokenStorageInterface
// and/or a PSR-3 Logger for debugging.
$garminConnect = new GarminConnect();
// call login with username and pwd
$connectResult = $garminConnect->login("<user>", "<pwd>");

// Check status
if ($connectResult->status === ConnectStatus::Connected) {
    // MFA is not enabled, you're done.
    $connectApi = $connectResult->connectApi;
    // Query some data
    $response = $connectApi->runQuery(GarminConnectQueryFactory::searchActivities());
    // Get the response result
    $data = $response->toJson();
}

if ($connectResult->status === ConnectStatus::MultiFactorAuthorizationRequired) {
    // MFA is enabled, show login form (incomplete example):
    echo '<form method="post" action="mfa">
      <input type="text" name="mfa" placeholder="MFA code" autofocus />
      <input type="hidden" name="csrf" value="' . $result->csrfToken . '" />
      <button type="submit">Login</button>
    </form>';
}

```

In case of MFA after form submission (mfa.php):

```php
<?php

require 'vendor/autoload.php';

use Sportlog\GarminConnect\ConnectStatus;
use Sportlog\GarminConnect\GarminConnect;
use Sportlog\GarminConnect\Queries\GarminConnectQueryFactory;

$mfaCode = $_REQUEST['mfa'];
$csrfToken = $_REQUEST['csrf'];

// resume login with MFA code and CSRF-token from pevious login() call
$connectApi = $garminConnect->resumeLogin("<user>", $mfaCode, $csrfToken);
// Query some data
$response = $connectApi->runQuery(GarminConnectQueryFactory::searchActivities());
// Get the response result
$data = $response->toJson();
```
