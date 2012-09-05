<?php

namespace Ob\LogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ob\LogBundle\Entity\Event
 */
class Event
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $objectClass
     */
    private $objectClass;

    /**
     * @var integer $objectId
     */
    private $objectId;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $env
     */
    private $env;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var array $data
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objectClass
     *
     * @param string $objectClass
     *
     * @return Event
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * Get objectClass
     *
     * @return string 
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     *
     * @return Event
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Event
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set env
     *
     * @param string $env
     *
     * @return Event
     */
    public function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Get env
     *
     * @return string 
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     *
     * @return Event
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return Event
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the entity's class without the namespace part
     *
     * @return mixed
     */
    public function getShortObjectClass()
    {
        $parts = explode('\\', $this->objectClass);

        return $parts[sizeof($parts) - 1];
    }
}