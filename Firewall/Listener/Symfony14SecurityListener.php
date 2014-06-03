<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener;

use Symfony\Component\HttpFoundation\Request;
use Theodo\Evolution\Bundle\SecurityBundle\UserProvider\LegacyUserRepositoryInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken;
use Theodo\Evolution\Bundle\SecurityBundle\Repository\Symfony14UserRepositoryInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\Symfony1\BagConfiguration;

/**
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @author Marek Kalnik <marekk@theodo.fr>
 * @author Cyrille Jouineau <cyrillej@theodo.fr>
 */
class Symfony14SecurityListener extends SecurityListener
{
    /**
     * {@inheritdoc}
     */
    protected function createToken(Request $request)
    {
        $authBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagConfiguration::AUTH_NAMESPACE));

        // Set the user and the authentication status according to the legacy session.
        if (false == $authBag->getValue()) {
            if (null !== $this->logger) {
                $this->logger->debug('The legacy user is not authenticated.');
            }

            if ($this->securityContext->getToken() instanceof EvolutionUserToken) {
                $this->setAnonymousToken();
            }

            return null;
        }

        // Create the token.
        $token = new EvolutionUserToken();
        if (!($username = $this->extractUsername($request))) {
            return null;
        }

        $token->setUser($username);
        $token->setAttribute('is_authenticated', $authBag->getValue());

        return $token;
    }

    /**
     * Extracts the username from request. Tries to find it in repository.
     * Returns null in case it was not found.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|mixed
     */
    public function extractUsername(Request $request)
    {
        $attributeBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagConfiguration::ATTRIBUTE_NAMESPACE));

        $user = $this->userProvider->loadUserById($attributeBag->get('symfony/user/sfUser/attributes.user_id'));

        if (!$user) {
            return null;
        }

        return $user->getUsername();
    }
}
