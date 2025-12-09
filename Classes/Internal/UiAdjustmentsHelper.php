<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Eel\ProtectedContextAwareInterface;

class UiAdjustmentsHelper implements ProtectedContextAwareInterface
{
    public function __construct(
        protected UiSessionInfoService $uiSessionInfoService
    )
    {
    }

    public function showMainMenu() {
        return $this->uiSessionInfoService->getUiSessionInfo()->showMainMenu;
    }

    public function showLeftSideBar() {
        return $this->uiSessionInfoService->getUiSessionInfo()->showLeftSideBar;
    }

    public function showEditPreviewDropDown() {
        return $this->uiSessionInfoService->getUiSessionInfo()->showEditPreviewDropDown;
    }

    public function allowsCallOfMethod($methodName) {
        return true;
    }
}
