<?php

namespace Sandstorm\NeosApi\Controller;

use Neos\ContentRepository\Core\Feature\WorkspaceModification\Command\ChangeBaseWorkspace;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService;
use Sandstorm\NeosApi\Auth\ApiJwtToken;
use Sandstorm\NeosApiClient\Internal\CreateOrUseExistingUserLoginCommand;
use Sandstorm\NeosApiClient\Internal\LoginCommandInterface;
use Sandstorm\NeosApiClient\Internal\SwitchBaseWorkspaceLoginCommand;

class EmbeddedBackendApiController extends ActionController
{
    #[Flow\Inject]
    protected Context $securityContext;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected WorkspaceService $workspaceService;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    public function openAction()
    {
        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            if ($token instanceof ApiJwtToken) {
                $jwt = $token->getCredentials()['jwt'];
                $key = 'secret'; // TODO: DO NOT HARDCODE!!!
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                foreach ($decoded->neos_cmd as $command) {
                    $className = $command->command;
                    $command = $className::fromStdClass($command);
                    assert($command instanceof LoginCommandInterface);

                    match(get_class($command)) {
                        SwitchBaseWorkspaceLoginCommand::class => $this->handleSwitchBaseWorkspace($command, $decoded->sub),
                    };
                }
            }
        }

        $this->redirectToUri('/neos');

    }

    private function handleSwitchBaseWorkspace(SwitchBaseWorkspaceLoginCommand $command, string $userName): void
    {
        $contentRepositoryId = ContentRepositoryId::fromString('default');
        //$this->workspaceService->createPersonalWorkspaceForUserIfMissing($siteDetectionResult->contentRepositoryId, $user);
        $user = $this->userService->getUser($userName, 'Sandstorm.NeosApi');

        $workspace = $this->workspaceService->getPersonalWorkspaceForUser($contentRepositoryId, $user->getId());
        if ($workspace->baseWorkspaceName->value === $command->baseWorkspace) {
            // Workspace already matches
            return;
        }

        // TODO: make CR ID dynamic
        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
        $contentRepository->handle(ChangeBaseWorkspace::create(
            $workspace->workspaceName,
            WorkspaceName::fromString($command->baseWorkspace)
        ));
    }
}
