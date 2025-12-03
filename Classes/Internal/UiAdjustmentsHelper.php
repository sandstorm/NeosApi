<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Eel\ProtectedContextAwareInterface;

class UiAdjustmentsHelper implements ProtectedContextAwareInterface
{

    public function __construct(
        protected UiSessionInfo $uiSessionInfo
    )
    {
    }

    public function showMainMenu() {
        return $this->uiSessionInfo->showMainMenu;
    }

    public function allowsCallOfMethod($methodName) {
        return true;
    }
}
