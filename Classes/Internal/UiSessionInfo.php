<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class UiSessionInfo
{
    public bool $showMainMenu;
    public bool $showLeftSideBar;
    public bool $showDocumentTree;
    public bool $showEditPreviewDropDown;
    public bool $showDimensionSwitcher;
    public ?string $notifyOnPublishTarget;

    function __construct()
    {
        $this->reset();
    }

    function reset() {
        $this->showMainMenu = true;
        $this->showLeftSideBar = true;
        $this->showDocumentTree = true;
        $this->showEditPreviewDropDown = true;
        $this->showDimensionSwitcher = true;
        $this->notifyOnPublishTarget = null;
    }
}
