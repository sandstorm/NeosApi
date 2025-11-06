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

    public function ensureSharedWorkspaceExistsCommand(string $workspaceName): string
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


    public function embeddedContentModuleCommand(string $user)
    {


        echo "\n\n";


        $this->outputLine('// Szenario: User ggf. anlegen; mit Zielworkspace-Override');
        echo $neos->ui->contentEditing(
            userName: $user
        )
            ->publishInto(workspace: 'live')
            ->buildUri();

        echo "\n\n";

        // Szenario: zu bestimmten Produkt-Node springen (user sieht aber weiterhin alles)
        $neos->ui->contentEditing(
            userName: NeosUser::createIfNotExisting('foo')
        )
            ->node('product-foo') # node ID
            ->buildUri();

        // Szenario: zu bestimmten Produkt-Node springen, und diesen ggf. neu anlegen if not existing (user sieht aber weiterhin alles)
        $neos->ui->contentEditing(
            user: NeosUser::createIfNotExisting('foo')
        )
            ->node('product-foo', createIfNotExisting: NodeCreation(nodeType: '....', parentNodeId: '....')) # node ID
            ->node('product-foo', createIfNotExisting: NodeCreation(nodeType: '....')) # node ID -> parentNodeId -> könnte als Default am NodeType stehen. ("ProductsFolder"?
            ->buildUri();

        // bvestimmte UI Elemente ein oder ausblenden
        $neos->ui->contentEditing(
            user: NeosUser::createIfNotExisting('foo')
        )
            ->documentNode('product-foo') # node ID
            ->dimension('....')
            ->editPreviewMode('mobile')
            ->hideMainMenu()
            ->minimalUi()
            ->buildUri();


        // Szenario: nur bestimmter Teilbaum sichtbar.
        $neos->ui->contentEditing(
            user: NeosUser::createIfNotExisting('foo')
        )
            ->nodeVisibility('product-foo') # subtreeTag
            ->buildUri();


        //->createNodeIfNotExisting(parent: ...)->forDocumentNode('my-product')->enableContentTree()->buildUri();


        echo $neos->ui->contentEditing()->buildUri();
        // echo NeosApiClient::create('http://127.0.0.1:8081')->embeddedContentModule()->buildUri();
    }
}
