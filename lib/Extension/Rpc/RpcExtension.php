<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\Extension\Rpc\Command\RpcCommand;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Handler\EchoHandler;
use Phpactor\Extension\Core\Rpc\StatusHandler;
use Phpactor\Extension\Completion\Rpc\CompleteHandler;
use Phpactor\Extension\Rpc\Handler\ClassSearchHandler;
use Phpactor\Extension\ClassMover\Rpc\ClassCopyHandler;
use Phpactor\Extension\ClassMover\Rpc\ClassMoveHandler;
use Phpactor\Extension\ClassMover\Rpc\ReferencesHandler;
use Phpactor\Extension\Rpc\Handler\OffsetInfoHandler;
use Phpactor\Extension\CodeTransform\Rpc\TransformHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassNewHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassInflectHandler;
use Phpactor\Extension\Rpc\Handler\ContextMenuHandler;
use Phpactor\Extension\CodeTransform\Rpc\ExtractConstantHandler;
use Phpactor\Extension\CodeTransform\Rpc\ExtractMethodHandler;
use Phpactor\Extension\CodeTransform\Rpc\GenerateMethodHandler;
use Phpactor\Extension\CodeTransform\Rpc\GenerateAccessorHandler;
use Phpactor\Extension\CodeTransform\Rpc\RenameVariableHandler;
use Phpactor\Extension\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Extension\Rpc\RequestHandler\LoggingHandler;
use Phpactor\Extension\CodeTransform\Rpc\OverrideMethodHandler;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\CodeTransform\Rpc\ImportClassHandler;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Container\Schema;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;

class RpcExtension implements Extension
{
    const SERVICE_REQUEST_HANDLER = 'rpc.request_handler';
    const TAG_RPC_HANDLER = 'rpc.handler';
    const TAG_RPC_HANDLER_REGISTRY = 'rpc.handler_registry';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('rpc.command.rpc', function (Container $container) {
            return new RpcCommand(
                $container->get('rpc.request_handler'),
                $container->get('config.paths'),
                $container->getParameter('rpc.store_replay')
            );
        }, [ 'ui.console.command' => [] ]);

        $container->register(self::SERVICE_REQUEST_HANDLER, function (Container $container) {
            return new LoggingHandler(
                new ExceptionCatchingHandler(
                    new RequestHandler($container->get('rpc.handler_registry'))
                ),
                $container->get('monolog.logger')
            );
        });

        $container->register('rpc.handler_registry', function (Container $container) {
            $handlers = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_RPC_HANDLER)) as $serviceId) {
                $handlers[] = $container->get($serviceId);
            }

            return new HandlerRegistry($handlers);
        });

        $this->registerHandlers($container);
    }

    private function registerHandlers(ContainerBuilder $container)
    {
        $container->register('rpc.handler.echo', function (Container $container) {
            return new EchoHandler();
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.complete', function (Container $container) {
            return new CompleteHandler(
                $container->get('application.complete')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.class_search', function (Container $container) {
            return new ClassSearchHandler(
                $container->get('application.class_search')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.class_references', function (Container $container) {
            return new ReferencesHandler(
                $container->get('reflection.reflector'),
                $container->get('application.class_references'),
                $container->get('application.method_references'),
                $container->get('source_code_filesystem.registry')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.copy_class', function (Container $container) {
            return new ClassCopyHandler(
                $container->get('application.class_copy')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.move_class', function (Container $container) {
            return new ClassMoveHandler(
                $container->get('application.class_mover'),
                $container->getParameter('rpc.class_move.filesystem')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.offset_info', function (Container $container) {
            return new OffsetInfoHandler(
                $container->get('reflection.reflector')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.transform', function (Container $container) {
            return new TransformHandler(
                $container->get('code_transform.transform')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.class_new', function (Container $container) {
            return new ClassNewHandler(
                $container->get('application.class_new')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.class_inflect', function (Container $container) {
            return new ClassInflectHandler(
                $container->get('application.class_inflect')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.context_menu', function (Container $container) {
            return new ContextMenuHandler(
                $container->get('reflection.reflector'),
                $container->get('application.helper.class_file_normalizer'),
                json_decode(file_get_contents(__DIR__ . '/menu.json'), true),
                $container
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.cache_clear', function (Container $container) {
            return new CacheClearHandler(
                $container->get('application.cache_clear')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.status', function (Container $container) {
            return new StatusHandler(
                $container->get('application.status'),
                $container->get('config.paths')
            );
        }, [ self::TAG_RPC_HANDLER => [] ]);

        $container->register('rpc.handler.config', function (Container $container) {
            return new ConfigHandler($container->getParameters());
        }, [ self::TAG_RPC_HANDLER => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
        $schema->setDefaults([
            'rpc.class_search.filesystem' => SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER,
            'rpc.class_move.filesystem' => SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            'rpc.store_replay' => false,
        ]);
    }
}
