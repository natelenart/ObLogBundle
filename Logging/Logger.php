<?php
namespace Ob\LogBundle\Logging;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;

use Ob\LogBundle\Entity\Event;

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
        // Get the id value
        $meta = $this->em->getClassMetadata(get_class($entity));
        $identifierField = $meta->getSingleIdentifierFieldName();
        $objectId = $meta->getReflectionProperty($identifierField)->getValue($entity);

        // Ensure event class implements EventInterface
        $event_class = $this->event_class;
        if ( ! $event_class instanceof EventInterface) {
          throw new InvalidArgumentException( // see the "use" statement for this at the top of the file
            sprintf('Event class "%s" must implement Ob\LogBundle\Entity\EventInterface', $event_class)
          );
        }

        // Create a new event (click/visit/print/whatever)
        $event = new $event_class;
        $event->setObjectClass(get_class($entity))
            ->setObjectId($objectId)
            ->setType($type)
            ->setEnv($this->env)
            ->setData($data);

        $this->em->persist($event);
        $this->em->flush();
    }
}
