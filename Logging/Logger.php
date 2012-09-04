<?php
namespace Ob\LogBundle\Logging;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

use Ob\LogBundle\Entity\Event;

/**
 * This class is used as a service to log clicks, prints, events, etc. and display meaningful statistics about them.
 */
class Logger
{
    protected $em;
    protected $env;


    /**
     * Init the the entity manager and the env
     *
     * @param EntityManager $em
     * @param string        $env
     */
    public function __construct(EntityManager $em, $env)
    {
        $this->em = $em;
        $this->env = $env;
    }


    /**
     * Logs an event
     *
     * @param object     $entity
     * @param string     $type
     * @param null|array $data
     */
    public function logEvent($entity, $type = 'visit', $data = null, $copyEntity = false)
    {
        // Get the id value
        $meta = $this->em->getClassMetadata(get_class($entity));
        $identifierField = $meta->getSingleIdentifierFieldName();
        $objectId = $meta->getReflectionProperty($identifierField)->getValue($entity);

        // Create a new event (click/visit/print/whatever)
        $event = new Event();
        $event->setObjectClass(get_class($entity))
            ->setObjectId($objectId)
            ->setType($type)
            ->setEnv($this->env)
            ->setCreatedAt(new \DateTime())
            ->setData($data);

        // Keep a copy, used for versioning, rollbacks, etc.
        if ($copyEntity) {
            $event->setObjectCopy($entity);
        }

        $this->em->persist($event);
        $this->em->flush();
    }
}