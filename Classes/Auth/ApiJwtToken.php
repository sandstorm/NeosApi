<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.15
 * Time: 07:12
 */

namespace Sandstorm\NeosApi\Auth;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\Token\AbstractToken;

class ApiJwtToken extends AbstractToken {

	/**
	 * The username credentials
	 * @var array
	 * @Flow\Transient
	 */
	protected $credentials = array('jwt' => '');



	/**
	 * Updates the username from environment vars
	 * are available. Sets the authentication status to AUTHENTICATION_NEEDED, if credentials have been sent.
	 *
	 * @param \Neos\Flow\Mvc\ActionRequest $actionRequest The current action request
	 * @return void
	 */
	public function updateCredentials(\Neos\Flow\Mvc\ActionRequest $actionRequest) {
        if ($actionRequest->hasArgument('neosapi_auth_jwt')) {
            $neosapiAuthJwt = $actionRequest->getArgument('neosapi_auth_jwt');

            if (!empty($neosapiAuthJwt) && $this->getAuthenticationStatus() !== self::AUTHENTICATION_SUCCESSFUL) {
                $this->credentials['jwt'] = $neosapiAuthJwt;
                $this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
            }
        }
	}

	/**
	 * Returns a string representation of the token for logging purposes.
	 *
	 * @return string The username credential
	 */
	public function  __toString() {
        return 'JWT: "' . $this->credentials['jwt'] . '"';
	}
}
