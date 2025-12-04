<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class UiSessionInfo
{
    public bool $showMainMenu;

    function __construct()
    {
        $this->reset();
    }

    function reset() {
        $this->showMainMenu = true;
    }
}
