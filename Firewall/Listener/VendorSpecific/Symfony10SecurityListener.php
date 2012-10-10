<?php

namespace Theodo\Evolution\SecurityBundle\Firewall\Listener\VendorSpecific;

use Symfony\Component\HttpFoundation\Request;
use Theodo\Evolution\HttpFoundationBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\SecurityBundle\Firewall\Listener\SecurityListener;
use Theodo\Evolution\SecurityBundle\Authentication\Token\EvolutionUserToken;

/**
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class Symfony10SecurityListener extends SecurityListener
{
    public function createToken(Request $request)
    {
        $authBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::AUTH_NAMESPACE));
        $attributeBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        // Set the user and the authentication status according to the legacy session.
        if (false == $authBag->getValue()
            || false == $attributeBag->has('sfGuardSecurityUser.username')
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
        $token->setUser($this->extractUsername($request));
        $token->setAttribute('is_authenticated', $authBag->getValue());

        return $token;
    }

    private function extractUsername(Request $request)
    {
        $attributeBag = $request->getSession()
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        return $attributeBag->get('sfGuardSecurityUser.username');
    }
}

