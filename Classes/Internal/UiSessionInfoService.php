<?php
declare(strict_types=1);
namespace Sandstorm\NeosApi\Internal;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Session\SessionManager;

#[Flow\Scope('singleton')]
class UiSessionInfoService
{
    private ?UiSessionInfo $uiSessionInfo = null;

    #[Flow\Inject]
    protected SessionManager $sessionManager;

    public function __construct()
    {
        Bootstrap::$staticObjectManager->registerShutdownObject($this, 'storeUiSessionInfo');
    }

    function getUiSessionInfo(): UiSessionInfo
    {
        if($this->uiSessionInfo === null) {
            $fromSession = $this->sessionManager->getCurrentSession()->getData(UiSessionInfo::class);
            if(!$fromSession instanceof UiSessionInfo) {
                $fromSession = new UiSessionInfo();
                $this->sessionManager->getCurrentSession()->putData(UiSessionInfo::class, $fromSession);
            }
            $this->uiSessionInfo = $fromSession;
        }
        return $this->uiSessionInfo;
    }

    function storeUiSessionInfo(): void
    {
        if($this->uiSessionInfo !== null) {
            $this->sessionManager->getCurrentSession()->putData(UiSessionInfo::class, $this->uiSessionInfo);
        }
    }
}
