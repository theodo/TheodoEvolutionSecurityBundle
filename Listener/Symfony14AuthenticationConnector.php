<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Theodo\Evolution\Bundle\SessionBundle\Manager\BagManagerConfigurationInterface;
use Theodo\Evolution\Bundle\SecurityBundle\Repository\Symfony14UserRepositoryInterface;

/**
 * This class should be registered on sucessful authentification.
 * It sets symfony1.4 sfGuard user as authentified.
 * If you need to affine your log in extend this class by adding custom checks.
 *
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class Symfony14AuthenticationConnector
{
    private $bagConfiguration;
    private $userRepository;
    private $session;

    /**
     * @param BagManagerConfigurationInterface $bagConfiguration
     * @param Symfony14UserRepositoryInterface $userRepository
     * @param SessionInterface $session
     */
    public function __construct(BagManagerConfigurationInterface $bagConfiguration, Symfony14UserRepositoryInterface $userRepository, SessionInterface $session)
    {
        $this->bagConfiguration = $bagConfiguration;
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    public function onSecurityAuthenticationSuccess(AuthenticationEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $session = $this->session;

        $user = $token->getUser();

        if (!$user || !$token->isAuthenticated()) {
            return;
        }

        $authBag = $session
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::AUTH_NAMESPACE));
        $authBag->set(true);
        $attributeBag = $session
            ->getBag($this->bagConfiguration->getNamespace(BagManagerConfigurationInterface::ATTRIBUTE_NAMESPACE));

        if (!is_object($user)) {
            $user = $this->userRepository->findOneByUsername($user);
        }

        $attributeBag->set('sfGuardSecurityUser.user_id', $user->getUserId());
    }
}

