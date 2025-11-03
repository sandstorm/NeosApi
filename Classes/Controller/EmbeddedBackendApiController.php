<?php

namespace Sandstorm\NeosApi\Controller;

use Neos\Flow\Annotations as Flow;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Neos\Domain\Service\UserService;
use Sandstorm\NeosApiClient\Internal\CreateOrUseExistingUserLoginCommand;
use Sandstorm\NeosApiClient\Internal\LoginCommandInterface;

class EmbeddedBackendApiController extends ActionController
{
    #[Flow\Inject]
    protected UserService $userService;
    public function openAction()
    {
        $this->redirectToUri('/neos');

    }

    private function handleCreateOrUseExistingUserLoginCommand(CreateOrUseExistingUserLoginCommand $command)
    {
        $user = $this->userService->getUser($command->userName, 'Sandstorm.NeosApi');
        if ($user === null) {
            $user = $this->userService->createUser(
                $command->userName,
                random_bytes(42), // ugly, nasty passwords
                $command->userName,
                '',
                null,
                'Sandstorm.NeosApi'
            );
            $this->persistenceManager->persistAll();
        }

    }
}
