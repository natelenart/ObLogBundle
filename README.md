# ObLogBundle

`ObLogBundle` by itself is really simple and only enables one to log basic events, but it is easily coupled with
`ObHighchartsBundle` to produce reports and charts of those tracked events. I use this bundle to track visits, ad prints,
clicks on buttons, clicks on ads and various other events accross websites.

The power of this simple bundles lies in the fact that an event can be linked to any entity and you can log custom
meta-data about the event or entity: referer, ip address, browser, etc. You could use this bundle to create an audit
trail of the changes in your backend, using the extra data to log the user and the type for the CRUD action performed.

## Installation

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

Register the bundle in your `app/AppKernel.php`:

``` php
    <?php
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
Here's how I would log the visit to the detail page of an article:

```php
    $article = $em->getRepository('ObPagesBundle:Article')->findOneBySlug($slug);

    ...

    $data = array("ip" => $this->get('request')->getClientIp());
    $this->get('ob_logger')->logEvent($article, 'visit', $data);

    ...
```

Here is an exmaple of a Listener to log CRUD action in a backend:

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