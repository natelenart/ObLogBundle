<?php
namespace Ob\LogBundle\Populator;

/**
 * This class is used to accept an event entity and populate its fields.
 *
 * If you create your own event entity, override this populator to correctly
 * populate your event object
 */
class EventPopulator {

    /**
     * Populate the given event object
     *
     * @param mixed $event
     */
    public function populate(&$event) {

        // Override this class and method to accept the event
        // and interact with it
        //
        // Object class, object id, type, env, and data are
        // populated automatically in Logger::logEvent
    }
}
