# ObLogBundle

`ObLogBundle` by itself is really simple and only enables one to log basic events, but it is easily coupled with
`ObHighchartsBundle` to produce reports and charts of those tracked events. I use this bundle to track visits, ad prints,
clicks on buttons, clicks on ads and various other events accross websites.

The power of this simple bundles lies in the fact that an event can be linked to any entity and you can log custom
meta-data about the event or entity: referer, ip address, browser, etc. You could use this bundle to create an audit
trail of the changes in your backend, using the extra data to log the user and the type for the CRUD action performed.

## Installation

### Symfony 2.0

Add the following lines to your `deps` file:

    [ObLogBundle]
        git=git://github.com/marcaube/ObLogBundle.git
        target=/bundles/Ob/LogBundle

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

Then configure the Autoloader

``` php
<?php
...
'Ob' => __DIR__.'/../vendor/bundles',
```

### Symfony 2.1

Add the following line to your composer.json file:

    "ob/log-bundle": "dev-master"

Update your dependencies:

``` bash
$ composer.phar update
```

### Finish Installation

Register the bundle in your `app/AppKernel.php`:

``` php
    ...
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Ob\LogBundle\ObLogBundle(),
            ...
        );
    ...
```

And finally, update your database:

``` bash
$ php app/console doctrine:schema:update --force
```

## Usage

### Example 1: Log a visit and ip from an article detail page:

```php
$article = $em->getRepository('ObPagesBundle:Article')->findOneBySlug($slug);

...

$data = array("ip" => $this->get('request')->getClientIp());
$this->get('ob_logger')->logEvent($article, 'visit', $data);

...
```

### Example 2: Create a Listener to log CRUD action in a backend:

``` php
// Ob/AdminBundle/Listener/ActionListener.php
namespace Ob\AdminBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Ob\LogBundle\Logging\Logger;
use Ob\LogBundle\Entity\Event;

class ActionListener
{
    /**
     * Create action
     */
    const ACTION_CREATE = 'create';

    /**
     * Update action
     */
    const ACTION_UPDATE = 'update';

    /**
     * Remove action
     */
    const ACTION_REMOVE = 'remove';

    /**
     * DIC
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;


    /**
     * Init
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Check for created entities
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->logEvent($args, self::ACTION_CREATE);
    }


    /**
     * Check for updated entities
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->logEvent($args, self::ACTION_UPDATE);
    }


    /**
     * Check for removed entities
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->logEvent($args, self::ACTION_REMOVE);
    }


    /**
     * Call the ObLogBundle Listener
     *
     * @param $args
     * @param $action
     */
    private function logEvent($args, $action)
    {
        if ($this->isLogged()) {
            $entity = $args->getEntity();

            // Don't log changes on Ob\LogBundle\Entity\Events
            if (!($entity instanceof Event)) {
                $data = array(
                    'user' => $this->container->get('security.context')->getToken()->getUser(),
                    'ip'    =>  $this->container->get('request')->getClientIp(),
                );
                $this->container->get('ob_logger')->logEvent($entity, $action, $data);
            }
        }
    }


    /**
     * Implement the checks you want, here we only check that there is /admin/ in the URI.
     *
     * @return bool
     */
    private function isLogged()
    {
        $uri = $this->container->get('request')->getUri();

        // If we are in the CMS
        if (preg_match('/\/admin\//', $uri)) {
            return true;
        }

        return false;
    }
}
```

And here is the service.yml that goes with it :

``` yaml
#  Ob/AdminBundle/Resources/config/service.yml

services:
    ob.admin_logger:
        class: Ob\AdminBundle\Listener\ActionListener
        arguments: [@service_container]
        tags:
            - { name: doctrine.event_listener, event: postPersist }
            - { name: doctrine.event_listener, event: postUpdate }
            - { name: doctrine.event_listener, event: postRemove }
```

### Example 3: Creating a custom Event class that stores the username responsible

1. Create new event class
1. Create new populator class
1. Register populator service and event class

#### Create the event class (leaving out ORM details)

``` bash
$ php app/console doctrine:generate:entity --entity=AcmeBlogBundle:LoggableEvent
```

``` php
// src/Acme/BlogBundle/Entity/Event.php

namespace Acme\BlogBundle\Entity\Event;

use Ob\LogBundle\Entity as Ob;

class LoggableEvent extends Ob\Event implements Ob\EventInterface
{
    private $username;

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }
}

```

#### Create the populator class
``` php
// src/Acme/BlogBundle/Populator/LoggableEventPopulator.php

namespace Acme\BlogBundle\Populator;

use Symfony\Component\Security\Core\SecurityContextInterface;

class LoggableEventPopulator
{
    private $username;

    public function __construct(SecurityContextInterface $context)
    {
        $this->username = $context->getToken()->getUser()->getUsername();
    }

    public function populate(&$event)
    {
        $event->setUsername($this->username);
        return $event;
    }
}
```

#### Register populator service and event class

``` yaml
# app/config/config.yml
...
ob_log:
  event_class: Acme\BlogBundle\Entity\Event\LoggableEvent
  event_populator_class: Acme\BlogBundle\Populator\LoggableEventPopulator

services:
  ob_log.event_populator:
    class: %ob_log.event.populator.class%
    arguments:
      - @security.context
```
