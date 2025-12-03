<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.15
 * Time: 07:11
 */

namespace Sandstorm\NeosApi\Auth;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Domain\Service\UserService;

class JwtAuthProvider extends AbstractProvider
{

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var \Neos\Flow\Security\AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;


    /**
     * @var PolicyService
     * @Flow\Inject
     */
    protected $policyService;


    /**
     * @var \Neos\Flow\Security\Context
     * @Flow\Inject
     */
    protected $securityContext;


    /**
     * @var ConfigurationManager
     * @Flow\Inject
     */
    protected $configurationManager;

    /**
     * Returns the class names of the tokens this provider can authenticate.
     *
     * @return array
     */
    public function getTokenClassNames()
    {
        return array(ApiJwtToken::class);
    }

    /**
     * Checks the given token for validity and sets the token authentication status
     * accordingly (success, wrong credentials or no credentials given).
     *
     * @param TokenInterface $authenticationToken The token to be authenticated
     * @return void
     * @throws \Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException
     */
    public function authenticate(TokenInterface $authenticationToken)
    {
        if (!($authenticationToken instanceof ApiJwtToken)) {
            throw new UnsupportedAuthenticationTokenException('This provider cannot authenticate the given token.', 1217339840);
        }

        $secret = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Sandstorm.NeosApi.Secret'
        );
        if($secret === null) throw new \RuntimeException('NeosApi.Secret settings not found');
        if(!is_string($secret)) throw new \RuntimeException('NeosApi.Secret settings must be a string');

        /** @var $account \Neos\Flow\Security\Account */
        $account = NULL;
        $credentials = $authenticationToken->getCredentials();

        if (is_array($credentials) && isset($credentials['jwt'])) {
            $jwt = $credentials['jwt'];
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));

            $userName = $decoded->sub;

            $user = $this->userService->getUser($userName, 'Sandstorm.NeosApi');
            if ($user === null) {
                $user = $this->userService->createUser(
                    $userName,
                    random_bytes(42), // ugly, nasty passwords
                    $userName,
                    '',
                    ['Neos.Neos:Editor'],
                    'Sandstorm.NeosApi'
                );
                $this->persistenceManager->persistAll();
            }

            $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);

            $account = $user->getAccounts()->first();
            $authenticationToken->setAccount($account);
        } else {
            $authenticationToken->setAuthenticationStatus(TokenInterface::NO_CREDENTIALS_GIVEN);
        }
    }
}
