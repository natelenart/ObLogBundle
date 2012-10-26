<?php

namespace Ob\LogBundle\Entity;

/**
 * Contains method signatures an Event object must implement
 */
interface EventInterface {

  public function setObjectClass($objectClass);
  public function setObjectId($objectId);
  public function setType($type);
  public function setEnv($env);
  public function setCreatedAt($createdAt);
  public function setData($data);
}
