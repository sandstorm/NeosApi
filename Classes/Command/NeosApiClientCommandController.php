<?php

declare(strict_types=1);

namespace Sandstorm\NeosApi\Command;

use Neos\Flow\Cli\CommandController;
use Sandstorm\NeosApiClient\NeosApiClient;

class NeosApiClientCommandController extends CommandController
{
    public function embeddedContentModuleCommand(string $user) {
        $neos = NeosApiClient::create('http://127.0.0.1:8081', 'secret');
        //$neos->ping();
        $this->outputLine('Einfachster Use Case: Neos öffnen mit existierendem Uswer');
        echo $neos->ui->contentEditing(userName: $user)
            ->buildUri();

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
