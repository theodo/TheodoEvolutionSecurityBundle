<?php
namespace Theodo\Evolution\Bundle\SecurityBundle\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * This class handles password validation in the same manner as the famous
 * sfGuardPlugin for Symfony 1. It's main purpose is to serve as a security compatibility layer
 * when migrating from Symfony 1 to Symfony2.
 *
 * When you finish the migration of all security functions of your legacy application it
 * is strongly suggested you migrate your user management to another bundle.
 *
 * WARNINGS:
 * 1. This class does not handle sf_guard_plugin_check_password_callable option.
 * If your legacy project had it specified, you'll need to create your own Encoder.
 *
 * @author Marek Kalnik <marekk@theodo.fr>
 */
class CallablePasswordEncoder implements PasswordEncoderInterface
{
    private $algorithm;

    /**
     * Configures the Encoder
     *
     * @throws \InvalidArgumentException
     * @param  callable $algorithm
     */
    public function __construct($algorithm)
    {
        if (!is_callable($algorithm)) {
            throw new \InvalidArgumentException('Password encoding algorithm has to be a callable.');
        }

        $this->algorithm = $algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        return call_user_func($this->algorithm, $salt.$raw);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded == call_user_func($this->algorithm, $salt.$raw);
    }
}
