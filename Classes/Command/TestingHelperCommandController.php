<?php

declare(strict_types=1);

namespace Sandstorm\NeosApi\Command;

use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Neos\Domain\Model\WorkspaceDescription;
use Neos\Neos\Domain\Model\WorkspaceRole;
use Neos\Neos\Domain\Model\WorkspaceRoleAssignment;
use Neos\Neos\Domain\Model\WorkspaceRoleAssignments;
use Neos\Neos\Domain\Model\WorkspaceTitle;
use Neos\Neos\Domain\Repository\WorkspaceMetadataAndRoleRepository;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService;
use org\bovigo\vfs\vfsStreamZipTestCase;
use Sandstorm\NeosApiClient\NeosApiClient;

class TestingHelperCommandController extends CommandController
{
    #[Flow\Inject]
    protected UserService $userDomainService;
    #[Flow\Inject]
    protected WorkspaceService $workspaceService;
    #[Flow\Inject]
    protected WorkspaceMetadataAndRoleRepository $metadataAndRoleRepository;

    private NeosApiClient $neosApi;

    public function __construct()
    {
        parent::__construct();
        $this->neosApi = NeosApiClient::create('http://127.0.0.1:8081', 'secret');
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

    public function contentEditingUriCommand(string $user): string
    {
        return $this->neosApi->ui->contentEditing(userName: $user)
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
        return $this->neosApi->ui->contentEditing(userName: $user)
            ->publishInto(workspace: $baseWorkspace)
            ->buildUri();
    }

    public function contentEditingUriWithNodeCommand(string $user, string $nodeAggregateId): string
    {
        return $this->neosApi->ui->contentEditing(userName: $user)
            ->node(nodeId: $nodeAggregateId)
            ->buildUri();
    }


    /**
     * @param string $user
     * @param array<string,string> $dimensions
     * @return string
     */
    public function contentEditingUriWithSwitchDimensionCommand(string $user, array $dimension): string
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

        return $this->neosApi->ui->contentEditing(userName: $user)
            ->dimensions(dimensions: $dimensions)
            ->buildUri();
    }


    public function embeddedContentModuleCommand(
        string $user,
        string $workspace = 'live',
    )
    {
        $this->outputLine('// Szenario: User ggf. anlegen; mit Zielworkspace-Override');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->publishInto(workspace: 'live')
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: Dimension wählen');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->publishInto(workspace: 'live')
                ->dimensions(dimensions: ['language' => 'de'])
                ->buildUri()
        );

        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: zu bestimmten Produkt-Node springen (user sieht aber weiterhin alles)');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->node('14ccf43a-562a-c9f7-2fd1-733e27068524') # Text & images
                ->buildUri()
        );
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->dimensions(dimensions: ['language' => 'en_UK'])
                ->node('82c24d81-51fa-87e5-b4ec-73eb505cb826') # Other elements
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: zu bestimmten Produkt-Node springen, und diesen ggf. neu anlegen if not existing (user sieht aber weiterhin alles)');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->node('product-foo', createIfNotExisting: NodeCreation(nodeType: '....', parentNodeId: '....')) # node ID
                ->node('product-foo', createIfNotExisting: NodeCreation(nodeType: '....')) # node ID -> parentNodeId -> könnte als Default am NodeType stehen. ("ProductsFolder"?
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// bestimmte UI Elemente ein oder ausblenden');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->documentNode('product-foo') # node ID
                ->dimension('....')
                ->editPreviewMode('mobile')
                ->hideMainMenu()
                ->minimalUi()
                ->buildUri()
        );

        $this->outputLine("\n\n");

        $this->outputLine('// Szenario: nur bestimmter Teilbaum sichtbar.');
        $this->outputLine(
            $this->neosApi->ui
                ->contentEditing(userName: $user)
                ->nodeVisibility('product-foo') # subtreeTag
                ->buildUri()
        );

        $this->outputLine("\n\n");


        //->createNodeIfNotExisting(parent: ...)->forDocumentNode('my-product')->enableContentTree()->buildUri();


        $this->outputLine($this->neosApi->ui->contentEditing($user)->buildUri());
        // echo NeosApiClient::create('http://127.0.0.1:8081')->embeddedContentModule()->buildUri();
    }
}
