<?php

namespace Theodo\Evolution\Bundle\SecurityBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * UserIdNotFoundException
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class UserIdNotFoundException extends AuthenticationException
{
    private $id;

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return sprintf('User with id "%s" could not be found.', $this->id);
    }

    /**
     * Get the user id.
     *
     * @return integer
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Set the user id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->id, $parentData) = unserialize($str);

        parent::unserialize($parentData);
    }
}
 