<?php

namespace Phpactor\Extension\ContextMenu;

use Phpactor\Extension\ContextMenu\Handler\ContextMenuHandler;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class ContextMenuExtension implements Extension
{
    const SERVICE_REQUEST_HANDLER = 'rpc.request_handler';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('rpc.handler.context_menu', function (Container $container) {
            return new ContextMenuHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('application.helper.class_file_normalizer'),
                json_decode(file_get_contents(__DIR__ . '/menu.json'), true),
                $container
            );
        }, [ 'rpc.handler' => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
