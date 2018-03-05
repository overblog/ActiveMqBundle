OverblogActiveMqBundle
======================

ActiveMQ Bundle for the Symfony.

Installation
--------------

```
composer req overblog/activemq-bundle
```

Usage
----------

Define publishers and consumers:

```yaml
overblog_active_mq:
    connections:
        default:
            user: '%env(MQ_USER)%'
            password: '%env(MQ_PASSWORD)%'
            servers: '%mq_servers%'

    publishers:
        foo:
            connection: default
            options:
                type: queue
                name: foo.notification
        bar:
            connection: default
            options:
                type: queue
                name: bar.notification
    consumers:
        foo:
            connection: default
            handler: App\Consumer\FooConsumer
            options:
                type: queue
                name: foo.notification # queue name
                prefetchSize: 10
        bar:
            connection: default
            handler: App\Consumer\BarConsumer
            options:
                type: queue
                name: bar.notification
                prefetchSize: 10

services:
    App\Consumer\BarConsumer: ~
    App\Consumer\FooConsumer: ~
```

An example of how to publish a message:
you can access publisher using service id `overblog_active_mq.{PUBLISHER_NAME}` for example
`overblog_active_mq.foo` or `overblog_active_mq.bar` for above configuration. Services is not public so create a public alias or inject it.
```php
<?php

namespace App\Controller;

use Overblog\ActiveMqBundle\ActiveMq\Publisher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BazController
{
    public function webhookCallbackAction(Request $request, Publisher $publisher)
    {
        $content = trim($request->getContent());
        $publisher->publish($content);

        return new Response('Successful notification', 200);
    }
}
```

An example of a consumer:
```php
<?php

namespace App\Consumer;

use Overblog\ActiveMqBundle\ActiveMq\ConsumerInterface;
use Overblog\ActiveMqBundle\ActiveMq\Message;

class FooConsumer extends Consumer
{
    /**
     * {@inheritdoc}
     */
    public function execute(Message $message)
    {
        // here you treat your message. Return false to noack the message.
    }
}
```
