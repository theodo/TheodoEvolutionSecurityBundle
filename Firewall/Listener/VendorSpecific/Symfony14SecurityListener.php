<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener\VendorSpecific;

use Symfony\Component\HttpFoundation\Request;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Firewall\Listener\SecurityListener;
use Theodo\Evolution\Bundle\SecurityBundle\Authentication\Token\EvolutionUserToken;
use Theodo\Evolution\Bundle\SecurityBundle\Repository\Symfony14UserRepositoryInterface;

/**
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @author Marek Kalnik <marekk@theodo.fr>
 * @author Cyrille Jouineau <cyrillej@theodo.fr>
 */
class Symfony14SecurityListener extends SecurityListener
{
    private $userRepository;

    public function createToken(Request $request)
    {
        $authBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::AUTH_NAMESPACE));
        $attributeBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        // Set the user and the authentication status according to the legacy session.
        if (false == $authBag->getValue()
            || false == $attributeBag->has('sfGuardSecurityUser.user_id')
        ) {
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

    public function setUserRepository(Symfony14UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserRepository()
    {
        return $this->userRepository;
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
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        $user = $this->userRepository->findOneByUserId($attributeBag->get('sfGuardSecurityUser.user_id'));

        if (!$user) {
            return null;
        }

        return $user->getUsername();
    }
}
