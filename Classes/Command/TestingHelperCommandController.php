<?php

declare(strict_types=1);

namespace Sandstorm\NeosApi\Command;

use Neos\ContentRepository\Core\Feature\NodeRemoval\Command\RemoveNodeAggregate;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Neos\Domain\Model\WorkspaceDescription;
use Neos\Neos\Domain\Model\WorkspaceRole;
use Neos\Neos\Domain\Model\WorkspaceRoleAssignment;
use Neos\Neos\Domain\Model\WorkspaceRoleAssignments;
use Neos\Neos\Domain\Model\WorkspaceTitle;
use Neos\Neos\Domain\Repository\WorkspaceMetadataAndRoleRepository;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService;
use Sandstorm\NeosApiClient\Internal\NodeCreation;
use Sandstorm\NeosApiClient\Internal\PreviewMode;
use Sandstorm\NeosApiClient\NeosApiClient;

class TestingHelperCommandController extends CommandController
{
    #[Flow\Inject]
    protected UserService $userDomainService;

    #[Flow\Inject]
    protected WorkspaceService $workspaceService;

    #[Flow\Inject]
    protected WorkspaceMetadataAndRoleRepository $metadataAndRoleRepository;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected ConfigurationManager $configurationManager;

    private ?NeosApiClient $neosApi = null;

    private function getNeosApi(): NeosApiClient
    {
        if($this->neosApi !== null) {
            return $this->neosApi;
        }

        $secret = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Sandstorm.NeosApi.Secret'
        );
        if($secret === null) throw new \RuntimeException('NeosApi.Secret settings not found');
        if(!is_string($secret)) throw new \RuntimeException('NeosApi.Secret settings must be a string');

        $this->neosApi = NeosApiClient::create('http://127.0.0.1:8081', $secret);
        return $this->neosApi;
    }

    public function removeUserIfExistsCommand(string $userName)
    {
        $user = $this->userDomainService->getUser($userName);
        if ($user !== null) {
            $this->outputLine('Removing user ' . $userName . ' with ID ' . $user->getId()->value);
            $this->userDomainService->deleteUser($user);
        } else {
            $this->outputLine('Did not find user ' . $userName . ', not removing...');
        }
    }

    public function removeNodeIfExistsCommand(string $workspace, string $nodeAggregateId)
    {
        $contentRepository = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'));
        $workspaceName = WorkspaceName::fromString($workspace);
        $nodeId = NodeAggregateId::fromString($nodeAggregateId);

        if($contentRepository->findWorkspaceByName($workspaceName) === null) {
            $this->outputLine('Could not find workspace ' . $workspaceName . ', not removing node ' . $nodeAggregateId . '...');
            return;
        }

        $node = $contentRepository->getContentGraph($workspaceName)
            ->findNodeAggregateById($nodeId);

        if ($node !== null) {
            $this->outputLine('Removing node ' . $nodeAggregateId . ' in workspace ' . $workspace);
            $anyCoveredDimension = $node->coveredDimensionSpacePoints->offsetGet(
                $node->coveredDimensionSpacePoints->getPointHashes()[0]
            );
            $contentRepository->handle(RemoveNodeAggregate::create(
                workspaceName: $workspaceName,
                nodeAggregateId: $nodeId,
                coveredDimensionSpacePoint: $anyCoveredDimension,
                nodeVariantSelectionStrategy: NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS,
            ));
        } else {
            $this->outputLine('Did not find node ' . $nodeAggregateId . ' in workspace ' . $workspace . ', not removing...');
        }
    }

    public function contentEditingUriCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->buildUri();
    }

    public function ensureSharedWorkspaceExistsCommand(string $workspaceName)
    {
        $cr = ContentRepositoryId::fromString('default');
        $wn = WorkspaceName::fromString($workspaceName);

        if ($this->metadataAndRoleRepository->loadWorkspaceMetadata($cr, $wn) !== null) {
            $this->outputLine('Workspace ' . $workspaceName . ' already exists, not creating...');
            exit();
        }
        $this->workspaceService->createSharedWorkspace(
            $cr,
            $wn,
            WorkspaceTitle::fromString($workspaceName),
            WorkspaceDescription::fromString($workspaceName),
            WorkspaceName::forLive(),
            WorkspaceRoleAssignments::create(
                WorkspaceRoleAssignment::createForGroup(
                    'Neos.Neos:AbstractEditor',
                    WorkspaceRole::COLLABORATOR,
                )
            )
        );
    }

    public function contentEditingUriWithSwitchBaseWorkspaceCommand(string $user, string $baseWorkspace): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->publishInto(workspace: $baseWorkspace)
            ->buildUri();
    }

    public function contentEditingUriWithNodeCommand(string $user, string $nodeAggregateId): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->node(nodeId: $nodeAggregateId)
            ->buildUri();
    }


    /**
     * @param string $user
     * @param string[] $dimension
     * @return string
     */
    public function contentEditingUriWithSwitchDimensionCommand(string $user, array $dimension): string
    {
        $dimensions = $this->splitDimensionArray($dimension);

        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->dimensions(dimensions: $dimensions)
            ->buildUri();
    }

    public function contentEditingUriWithCreateNodeIfNotExistsCommand(string $user, string $nodeAggregateId, string $nodeType, string $parentNodeAggregateId): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->node(nodeId: $nodeAggregateId, createIfNotExisting: new NodeCreation(nodeType: $nodeType, parentNodeId: $parentNodeAggregateId))
            ->buildUri();
    }

    /**
     * @param string $user
     * @param string $nodeAggregateId
     * @param string[] $dimension
     * @return string
     */
    public function contentEditingUriNodeSelectionBeforeDimensionSelectionCommand(string $user, string $nodeAggregateId, array $dimension): string
    {
        $dimensions = $this->splitDimensionArray($dimension);

        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->node(nodeId: $nodeAggregateId)
            ->dimensions(dimensions: $dimensions)
            ->buildUri();
    }

    public function contentEditingWithHiddenMainMenuCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->hideMainMenu()
            ->buildUri();
    }

    public function contentEditingWithHiddenLeftSideBarCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->hideLeftSideBar()
            ->buildUri();
    }

    public function contentEditingWithMinimalUiCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->minimalUi()
            ->buildUri();
    }

    public function contentEditingUriWithPreviewModeCommand(string $user, string $previewMode): string
    {
        $previewMode = PreviewMode::fromString($previewMode);

        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->editPreviewMode($previewMode)
            ->buildUri();
    }

    public function contentEditingUriWithHiddenEditPreviewModeDropDownCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->hideEditPreviewDropDown()
            ->buildUri();
    }

    public function contentEditingUriWithHiddenDimensionSwitcherCommand(string $user): string
    {
        return $this->getNeosApi()->ui->contentEditing(userName: $user)
            ->hideDimensionSwitcher()
            ->buildUri();
    }

    public function embeddedContentModuleCommand(
        string $user,
        string $workspace = 'live',
    )
    {
        $this->outputLine('// Szenario: User ggf. anlegen; mit Zielworkspace-Override');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->publishInto(workspace: 'live')
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: Dimension wählen');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->publishInto(workspace: 'live')
                ->dimensions(dimensions: ['language' => 'de'])
                ->buildUri()
        );

        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: zu bestimmten Produkt-Node springen (user sieht aber weiterhin alles)');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->node('14ccf43a-562a-c9f7-2fd1-733e27068524') # Text & images
                ->buildUri()
        );
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->node('82c24d81-51fa-87e5-b4ec-73eb505cb826') # Other elements
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: zu bestimmten Produkt-Node springen, und diesen ggf. neu anlegen if not existing (user sieht aber weiterhin alles)');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->node('product-foo', createIfNotExisting: new NodeCreation(nodeType: '....', parentNodeId: '....')) # node ID
                ->node('product-foo', createIfNotExisting: new NodeCreation(nodeType: '....')) # node ID -> parentNodeId -> könnte als Default am NodeType stehen. ("ProductsFolder"?
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// bestimmte UI Elemente ein oder ausblenden');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->hideMainMenu()
                ->node('product-foo') # node ID
                ->dimensions(['....'])
                ->minimalUi()
                ->editPreviewMode('mobile')
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: nur bestimmter Teilbaum sichtbar.');
        $this->outputLine(
            $this->getNeosApi()->ui
                ->contentEditing(userName: $user)
                ->nodeVisibility('product-foo') # subtreeTag
                ->buildUri()
        );

        $this->outputLine("\n\n");


        //->createNodeIfNotExisting(parent: ...)->forDocumentNode('my-product')->enableContentTree()->buildUri();


        $this->outputLine($this->getNeosApi()->ui->contentEditing($user)->buildUri());
        // echo NeosApiClient::create('http://127.0.0.1:8081')->embeddedContentModule()->buildUri();
    }

    /**
     * @param string[] $dimension
     * @return array<string,string>
     */
    private function splitDimensionArray(array $dimension): array
    {
        $dimensions = [];
        foreach ($dimension as $pair) {
            $exploded = explode(':', $pair, 2);
            if(count($exploded) !== 2) {
                throw new \InvalidArgumentException("--dimension must have from <key>:<value>, \"$pair\" was given. Did you use \"=\" instead of \":\"?");
            }
            [$key, $value] = $exploded;
            $dimensions[$key] = $value;
        }
        return $dimensions;
    }
}
