<?php

namespace Sandstorm\NeosApi\Controller;

use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\WorkspaceModification\Command\ChangeBaseWorkspace;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\NodeTypeNameFactory;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService;
use Neos\Neos\FrontendRouting\SiteDetection\SiteDetectionResult;
use Sandstorm\NeosApi\Auth\ApiJwtToken;
use Sandstorm\NeosApiClient\Internal\LoginCommandInterface;
use Sandstorm\NeosApiClient\Internal\SwitchBaseWorkspaceLoginCommand;
use Sandstorm\NeosApiClient\Internal\SwitchDimensionLoginCommand;
use Sandstorm\NeosApiClient\Internal\SwitchEditedNodeLoginCommand;

class EmbeddedBackendApiController extends ActionController
{
    #[Flow\Inject]
    protected Context $securityContext;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected WorkspaceService $workspaceService;

    #[Flow\Inject]
    protected SiteRepository $siteRepository;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    private function getBaseNodeAddress(string $userName): NodeAddress
    {
        $siteDetectionResult = SiteDetectionResult::fromRequest($this->request->getHttpRequest());
        $contentRepositoryId = $siteDetectionResult->contentRepositoryId;

        $user = $this->userService->getUser($userName, 'Sandstorm.NeosApi');
        $this->workspaceService->createPersonalWorkspaceForUserIfMissing($contentRepositoryId, $user);
        $workspace = $this->workspaceService->getPersonalWorkspaceForUser($contentRepositoryId, $user->getId());

        $site = $this->siteRepository->findOneByNodeName($siteDetectionResult->siteNodeName);
        $dimensionSpacePoint = $site?->getConfiguration()->defaultDimensionSpacePoint;

        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
        $contentGraph = $contentRepository->getContentGraph($workspace->workspaceName);

        // we assume that the ROOT node is always stored in the CR as "physical" node; so it is safe
        // to call the contentGraph here directly.
        $rootNodeAggregate = $contentGraph->findRootNodeAggregateByType(
            NodeTypeNameFactory::forSites()
        );
        if (!$rootNodeAggregate) {
            throw new \RuntimeException(sprintf('No sites root node found in content repository "%s", while fetching site node "%s"', $contentRepository->id->value, $siteDetectionResult->siteNodeName->value), 1764150812);
        }

        $siteNodeAggregate = $contentGraph->findChildNodeAggregateByName(
            $rootNodeAggregate->nodeAggregateId,
            $siteDetectionResult->siteNodeName->toNodeName(),
        );

        return NodeAddress::create(
            $contentRepository->id,
            $workspace->workspaceName,
            $dimensionSpacePoint,
            $siteNodeAggregate->nodeAggregateId,
        );
    }

    public function openAction()
    {
        $nodeAddress = null;

        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            if ($token instanceof ApiJwtToken) {
                $jwt = $token->getCredentials()['jwt'];
                $key = 'secret'; // TODO: DO NOT HARDCODE!!!
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

                $nodeAddress = $nodeAddress ?? $this->getBaseNodeAddress($decoded->sub);

                foreach ($decoded->neos_cmd as $command) {
                    $className = $command->command;
                    $command = $className::fromStdClass($command);
                    assert($command instanceof LoginCommandInterface);

                    $nodeAddress = match(get_class($command)) {
                        SwitchBaseWorkspaceLoginCommand::class => $this->handleSwitchBaseWorkspace($command, $decoded->sub, $nodeAddress),
                        SwitchDimensionLoginCommand::class => $this->handleSwitchDimension($command, $decoded->sub, $nodeAddress),
                        SwitchEditedNodeLoginCommand::class => $this->handleSwitchEditedNode($command, $decoded->sub, $nodeAddress),
                    };
                }
            }
        }

        $urlParam = $nodeAddress ? '?node=' . urlencode($nodeAddress?->toJson()) : '';
        $this->redirectToUri('/neos/content' . $urlParam);
    }

    private function handleSwitchBaseWorkspace(
        SwitchBaseWorkspaceLoginCommand $command,
        string $userName,
        NodeAddress $nodeAddress
    ): NodeAddress
    {
        $contentRepository = $this->contentRepositoryRegistry->get($nodeAddress->contentRepositoryId);
        $workspace = $contentRepository->findWorkspaceByName($nodeAddress->workspaceName);
        if ($workspace->baseWorkspaceName->value === $command->baseWorkspace) {
            // Workspace already matches
            return $nodeAddress;
        }

        $contentRepository->handle(ChangeBaseWorkspace::create(
            $workspace->workspaceName,
            WorkspaceName::fromString($command->baseWorkspace)
        ));

        return $nodeAddress;
    }

    private function handleSwitchDimension(
        SwitchDimensionLoginCommand $command,
        string $userName,
        NodeAddress $nodeAddress
    ): NodeAddress
    {
        $targetDimensionSpacePoint = DimensionSpacePoint::fromArray($command->dimensions);
        if($nodeAddress->dimensionSpacePoint === $targetDimensionSpacePoint) {
            return $nodeAddress;
        }

        return NodeAddress::create(
            contentRepositoryId: $nodeAddress->contentRepositoryId,
            workspaceName: $nodeAddress->workspaceName,
            dimensionSpacePoint: $targetDimensionSpacePoint,
            aggregateId: $nodeAddress->aggregateId,
        );
    }

    private function handleSwitchEditedNode(
        SwitchEditedNodeLoginCommand $command,
        string                       $userName,
        NodeAddress                  $nodeAddress
    ): NodeAddress
    {
        $targetNodeAggregateId = NodeAggregateId::fromString($command->nodeId);
        if($nodeAddress->aggregateId === $targetNodeAggregateId) {
            return $nodeAddress;
        }

        return $nodeAddress->withAggregateId($targetNodeAggregateId);
    }
}
