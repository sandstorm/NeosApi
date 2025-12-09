<?php

namespace Sandstorm\NeosApi\Controller;

use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\WorkspaceModification\Command\ChangeBaseWorkspace;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\NodeTypeNameFactory;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService;
use Neos\Neos\FrontendRouting\SiteDetection\SiteDetectionResult;
use Sandstorm\NeosApi\Auth\ApiJwtToken;
use Sandstorm\NeosApi\Exceptions\RequestedNodeDoesNotExist;
use Sandstorm\NeosApi\Internal\UiSessionInfo;
use Sandstorm\NeosApi\Internal\UiSessionInfoService;
use Sandstorm\NeosApi\Internal\UnusedSessionInfo;
use Sandstorm\NeosApiClient\Internal\AdaptNeosUiLoginCommand;
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

    #[Flow\Inject]
    protected ConfigurationManager $configurationManager;

    #[Flow\Inject]
    protected UiSessionInfoService $uiSessionInfoService;

    #[Flow\Inject]
    protected UserService $userService;

    #[Flow\Inject]
    protected PersistenceManager $persistenceManager;

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
        $secret = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Sandstorm.NeosApi.Secret'
        );
        if($secret === null) throw new \RuntimeException('NeosApi.Secret settings not found');
        if(!is_string($secret)) throw new \RuntimeException('NeosApi.Secret settings must be a string');

        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            if ($token instanceof ApiJwtToken) {
                $jwt = $token->getCredentials()['jwt'];
                $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));

                $priority = fn($class) => match($class) {
                    SwitchBaseWorkspaceLoginCommand::class => 0,
                    SwitchDimensionLoginCommand::class => 0,
                    SwitchEditedNodeLoginCommand::class => 1,
                };
                usort($decoded->neos_cmd, fn ($a, $b) => $priority($a->command) <=> $priority($b->command));

                $uiSessionInfo = $this->uiSessionInfoService->getUiSessionInfo();
                $uiSessionInfo->reset();

                $nodeAddress = $nodeAddress ?? $this->getBaseNodeAddress($decoded->sub);

                foreach ($decoded->neos_cmd as $command) {
                    $className = $command->command;
                    $command = $className::fromStdClass($command);
                    assert($command instanceof LoginCommandInterface);

                    match(get_class($command)) {
                        SwitchBaseWorkspaceLoginCommand::class =>
                            $nodeAddress = $this->handleSwitchBaseWorkspace($command, $nodeAddress),
                        SwitchDimensionLoginCommand::class =>
                            $nodeAddress = $this->handleSwitchDimension($command, $nodeAddress),
                        SwitchEditedNodeLoginCommand::class =>
                            $nodeAddress = $this->handleSwitchEditedNode($command, $nodeAddress),
                        AdaptNeosUiLoginCommand::class =>
                            $this->handleAdaptNeosUi($command, $decoded->sub, $uiSessionInfo),
                    };
                }

                // TODO: Session ggf. manuell maintainen (race condition)!!!! -> SessionManager

                $urlParam = '?node=' . urlencode($nodeAddress?->toJson());
                $this->redirectToUri('/neos/content' . $urlParam);
            }
        }

        throw new \RuntimeException('No JWT token found in security context');
    }

    private function handleSwitchBaseWorkspace(
        SwitchBaseWorkspaceLoginCommand $command,
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

    /**
     * @throws AccessDenied|RequestedNodeDoesNotExist
     */
    private function handleSwitchEditedNode(
        SwitchEditedNodeLoginCommand $command,
        NodeAddress                  $nodeAddress
    ): NodeAddress
    {
        $targetNodeAggregateId = NodeAggregateId::fromString($command->nodeId);
        if($nodeAddress->aggregateId === $targetNodeAggregateId) {
            return $nodeAddress;
        }

        $contentRepository = $this->contentRepositoryRegistry->get($nodeAddress->contentRepositoryId);
        $contentGraph = $contentRepository->getContentGraph($nodeAddress->workspaceName);
        $node = $contentGraph->findNodeAggregateById($targetNodeAggregateId);
        $nodeExists = $node !== null && $node->coversDimensionSpacePoint($nodeAddress->dimensionSpacePoint);

        if(!$nodeExists && $command->nodeCreation === null) {
            throw new RequestedNodeDoesNotExist();

        } else if(!$nodeExists && $command->nodeCreation !== null) {
            $parentNodeId = NodeAggregateId::fromString($command->nodeCreation->parentNodeId);
            $parentNode = $contentGraph->findNodeAggregateById($parentNodeId);
            if($parentNode === null || !$parentNode->coversDimensionSpacePoint($nodeAddress->dimensionSpacePoint)) {
                throw new RequestedNodeDoesNotExist();

            } else {
                $contentRepository->handle(CreateNodeAggregateWithNode::create(
                    workspaceName: $nodeAddress->workspaceName,
                    nodeAggregateId: $targetNodeAggregateId,
                    nodeTypeName: NodeTypeName::fromString($command->nodeCreation->nodeType),
                    originDimensionSpacePoint: OriginDimensionSpacePoint::fromDimensionSpacePoint($nodeAddress->dimensionSpacePoint),
                    parentNodeAggregateId: NodeAggregateId::fromString($command->nodeCreation->parentNodeId),
                ));
            }
        }

        return $nodeAddress->withAggregateId($targetNodeAggregateId);
    }

    function handleAdaptNeosUi(
        AdaptNeosUiLoginCommand $command,
        string $userName,
        UiSessionInfo $uiSessionInfo,
    )
    {
        $uiSessionInfo->showMainMenu = $command->showMainMenu ?? $uiSessionInfo->showMainMenu;
        $uiSessionInfo->showLeftSideBar = $command->showLeftSideBar ?? $uiSessionInfo->showLeftSideBar;
        $uiSessionInfo->showEditPreviewDropDown = $command->showEditPreviewDropDown ?? $uiSessionInfo->showEditPreviewDropDown;

        if($command->previewMode !== null) {
            $previewMode = $command->previewMode->value;
            $user = $this->userService->getUser($userName, 'Sandstorm.NeosApi');
            $user->getPreferences()->set("contentEditing.editPreviewMode", $previewMode);
            $this->userService->updateUser($user);
            $this->persistenceManager->persistAll();
        }
    }
}
