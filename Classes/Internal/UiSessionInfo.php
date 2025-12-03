<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

#[Flow\Scope('session')]
class UiSessionInfo
{
    public bool $showMainMenu = true;

    public function reset() {
        $this->showMainMenu = true;
    }
}
