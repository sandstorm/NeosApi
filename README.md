# sandstorm/neosapi

Package to control Neos from and embed it in your own non Neos projects. Send signed instructions to your Neos to
directly open the backend and edit the page for a specific use case.

This could be used to e.g. in an application containing both generated and redactional content, allowing you to let
Neos care about the redactional parts including multi-language support, editing UI, and so on while using another
project to manage users, login and non-redactional parts of the final product.

# Installation

Install this into your Neos with

```
composer require sandstorm/neosapi
```

Make sure to provide a secret by either overwriting the configuration setting `Sandstorm.NeosApi.Secret` or providing
a value for the environment variable `NEOS_API_SECRET` which is used by default.

This API can be called by functions in the `sandstorm/neosapiclient` package. You will need to install this in the 
project you want to be able to interact with Neos.

# Usage and Examples

Generate a new NeosApiClient. This assumes the secret is made available as an environment variable named 
`"NEOS_API_SECRET"`:

```php
$secret = getenv("NEOS_API_SECRET");
$neosApi = \Sandstorm\NeosApiClient\NeosApiClient::create("http://your-neos-domain.com", $secret);
```

This object can then be used to build URIs with time limited validity to directly edit content in the Neos backend. 
Further use cases including directly altering the Backend state are planned but not currently implemented.

### Example use cases

#### Editing a specific Node:

```php
$neosApi->ui->contentEditing(userName: $user)
        ->node(nodeId: $nodeAggregateId)
        ->buildUri();
```

#### Hiding parts of the Neos Ui

```php
$neosApi->ui->contentEditing(userName: $user)
        ->hideMainMenu()
        ->hideLeftSideBar()
        ->buildUri();
```

#### Further use cases

Further NeosApiClient usage are documented in the code. Also examples can be found in the 
`TestingHelperCommandController`: Most Commands there use the NeosApiClient package and implement a single use case 
with it. 

# Development

Required software:
- composer
- npm (to compile/minimize JavaScript and CSS sources)
- npx (for running playwright tests)

Developing this package requires a complete Neos instance. You can use the neos-development-distribution for this:

1. Clone the `neos-development-distribution`
   ```bash
   git clone git@github.com:neos/neos-development-distribution.git
   cd neos-development-distribution
   ```
2. Checkout the `9.0` branch
   ```bash
   git checkout 9.0
   ```
3. Clone `sandstorm/neosapi` ans `sandstorm/neosapiclient` into `DistributionPackages`
   ```bash
   git submodule add git@github.com:sandstorm/NeosApi.git DistributionPackages/NeosApi
   git submodule add git@github.com:sandstorm/NeosApi.git DistributionPackages/NeosApi
   ```
4. Install both packages as dev dependencies with composer and run composer install
   ```bash
   composer require "sandstorm/neosapiclient:@dev"
   composer require "sandstorm/neosapi:@dev"
   composer install
   ```
5. Start Neos using flow
   ```bash
   NEOS_API_SECRET=your-very-own-secret ./flow sandstorm.neosapi:testingHelper:contentEditingUriWithHiddenDocumentTree cli-use
   ```
6. Open the local Neos instance at http://127.0.0.1:8081 and follow the initial setup instructions
7. Test the setup with the TestingHelperCommandController
   ```bash
   NEOS_API_SECRET=your-very-own-secret ./flow sandstorm.neosapi:testingHelper:contentEditingUri test-user
   ```
   Flow should use the `sandstorm/neosapiclient` library to generate a URI for you
8. Open the provided URI. You should see a Neos Ui. The user you are logged in with should be `test-user`.
   
You are now ready to start coding.

## Editing JavaScript/CSS

Editing the Neos Ui requires custom JavaScript and CSS. The repo contains the already minimised js and css files to 
allow development of the php part without worrying about this at all. Should you want to edit any JavaScript and/or CSS
files you will need to compile them yourself. To do so use `npm`:

```bash
# run in .../DistributionPackages/NeosApi directory
npm run watch
```

## Architecture

NeosApi consists of a Neos package (**NeosApi**) and a standalone PHP library (**NeosApiClient**). These libaries are
developed in lockstep and are meant to work together to provide an API for interacting with Neos. 

Neos-specific code is kept in the NesoApi package while common and client side code is found within NeosApiClient.

### NeosApi (Neos Package)

NeosApi is a Neos package that provides an API endpoint inside a Neos installation. It receives and verifies incoming 
requests and contains all logic required to handle those requests, including performing state changes in Neos when 
necessary. Request verification is implemented in `NeosApi/Classes/Auth`, while `NeosApi/Classes/Controller` acts as 
the central API endpoint and orchestration layer.

### NeosApiClient (PHP Library)

NeosApiClient is a plain PHP library used by external systems to generate URIs that can be parsed by NeosApi. It 
encapsulates request and protocol logic without depending on Neos. 

Directly calling NeosApi endpoints from the client is a planned feature.

### Shared Internal Code

The directory `NeosApiClient/src/internal` contains internal code shared between the NeosApi package and the 
NeosApiClient library. This is mostly DTOs and serialization logic, required by both the NeosApiCient and NeosApi to 
build and then later parse the generated URI's.

## Testing

The package contains end-to-end tests written in playwright. To run the test use:

```bash
# run in .../DistributionPackages/NeosApi directory
NEOS_API_SECRET=your-very-own-secret npx playwright test
```

You can find the test specifications in the `Tests/Playwright` directory.

For further information on how to run or write playwright tests please use the contained tests as examples and refer to
the playwright documentation.
