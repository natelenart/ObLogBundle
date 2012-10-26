<?php
namespace Ob\LogBundle\Logging;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;

use Ob\LogBundle\Entity;

/**
 * This class is used as a service to log clicks, prints, events, etc. and display meaningful statistics about them.
 */
class Logger
{
    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var string $env
     */
    protected $env;

    /**
     * @var string $event_class
     */
    protected $event_class;

    /**
     * Init the the entity manager and the env
     *
     * @param EntityManager $em
     * @param string        $env
     * @param string        $event_class
     */
    public function __construct(EntityManager $em, $env, $event_class)
    {
        $this->em = $em;
        $this->env = $env;
        $this->event_class = $event_class;
    }

    /**
     * Logs an event
     *
     * @param object     $entity
     * @param string     $type
     * @param null|array $data
     */
    public function logEvent(&$entity, $type = 'visit', $data = null)
    {
        // Get the object id from the entity
        $objectId = $this->getObjectIdFromEntity($entity);

        // Grab a new Event instance
        $event = $this->generateEvent();

        // Attach data to the event
        $event
            ->setObjectClass(get_class($entity))
            ->setObjectId($objectId)
            ->setType($type)
            ->setEnv($this->env)
            ->setData($data)
            ;

        // Save the event in the database
        $this->save($event);
    }

    /**
     * Use reflection to extract the entity's id
     *
     * @param object $entity
     * @return mixed The object id
     */
    protected function getObjectIdFromEntity(&$entity)
    {
        $meta = $this->em->getClassMetadata(get_class($entity));
        $identifierField = $meta->getSingleIdentifierFieldName();
        $objectId = $meta->getReflectionProperty($identifierField)->getValue($entity);
        return $objectId;
    }

    /**
     * Generate a new event of the dependency-injected type
     *
     * @return mixed The generated event
     */
    protected function generateEvent()
    {
        // Generate a new event
        $event_class = $this->event_class;
        $event = new $event_class;

        // Ensure event supports the methods we need
        if ( ! $event instanceof Entity\EventInterface) {
          throw new InvalidArgumentException( // see the "use" statement for this at the top of the file
            sprintf('Event class "%s" must implement Ob\LogBundle\Entity\EventInterface', $event_class)
          );
        }

        return $event;
    }

    /**
     * Save the event in the database
     *
     * @param object $event
     */
    protected function save($event)
    {
        $this->em->persist($event);
        $this->em->flush();
    }
}
