<?php

namespace TheodoEvolution\SecurityBundle\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use TheodoEvolution\HttpFoundationBundle\Manager\Symfony10BagNamespaces;

/**
 * Symfony10EntryPoint class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony10EntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var String
     */
    private $loginPath;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param                                                            $loginPath
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct($loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session   = $session;
    }

    /**
     * Redirect the user to the legacy login path.
     *
     * @param Request                 $request       The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $this->prepareSession($request);

        $response = new RedirectResponse($this->loginPath);

        return $response;
    }

    /**
     * Set the signin url for the redirection after signin.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function prepareSession(Request $request)
    {
        $attributes = $this->session->getBag(Symfony10BagNamespaces::ATTRIBUTE_NAMESPACE);
        $attributes->set('symfony/user/sfUser/attributes.signin_url', $request->getUri());
        $attributes->set('symfony/user/sfUser/attributes.referer', $request->getUri());
        $this->session->save();
    }
}
