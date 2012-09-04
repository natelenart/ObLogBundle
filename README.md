# ObLogBundle

`ObLogBundle` by itself is really simple and only enables one to log basic events, but it is easily coupled with
`ObHighchartsBundle` to produce reports and charts of those tracked events. I use this bundle to track visits, ad prints,
clicks on buttons, clicks on ads and various other events accross websites.

The power of this simple bundles lies in the fact that an event can be linked to any entity and you can log custom
meta-data about the event or entity: referer, ip address, browser, etc. You could use this bundle to create an audit
trail of the changes in your backend, using the extra data to log the user and the type for the CRUD action performed.

You can also use the lifeCycle callbacks of your entities to log events and keep a trail of who did what and when in your
system.

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

If you want, you can even keep a copy of the object for backend versioning or other applications like that:

``` php
    // The last parameter tells the service to keep a copy of the entity, defaults to false for obvious reasons
    $this->get('ob_logger')->logEvent($article, 'delete', $data, true);
```