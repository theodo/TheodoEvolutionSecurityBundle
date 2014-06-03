<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\EntryPoint;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * LegacyEntryPoint
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LegacyEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var string
     */
    private $loginPath;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param string           $loginPath
     * @param SessionInterface $session
     */
    public function __construct($loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session   = $session;
    }

    /**
     * Starts the authentication scheme.
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($request->getBaseUrl().$this->loginPath);
    }
}
 