<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Providers;

use EchoFusion\Contracts\RouteManager\RouteLoaderInterface;
use EchoFusion\Contracts\RouteManager\RouteMatcherInterface;
use EchoFusion\Contracts\RouteManager\RouterInterface;
use EchoFusion\Contracts\ServiceManager\SettableContainerInterface;
use EchoFusion\Contracts\ServiceProvider\ServiceProviderInterface;
use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\RouteMatcher;
use EchoFusion\RouteManager\Router;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function sprintf;

class RouteManagerProvider implements ServiceProviderInterface
{
    /**
     * Lazy loading: Service is instantiated only when needed
     */
    public function register(SettableContainerInterface $container): void
    {
        $container->set(RouteMatcherInterface::class, function () {
            return new RouteMatcher();
        });

        $container->set(RouterInterface::class, function () use ($container) {
            return new Router(
                $container->get(RouteMatcherInterface::class)
            );
        });
    }

    /**
     * Boot the services by given config
     *
     * @throws DuplicateRouteException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(SettableContainerInterface $container, array $config = []): void
    {
        // Load default config from stub if no config is provided
        if (empty($config)) {
            $config = require __DIR__ . '/../../config/routemanager.config.php';
        }

        $router = $container->get(RouterInterface::class);

        if (!$router instanceof RouteLoaderInterface) {
            throw new LogicException(sprintf('The router must implement %s to load routes from configuration.', RouteLoaderInterface::class));
        }

        $router->fromArray($config);

        // persist changes on container
        $container->set(RouterInterface::class, fn () => $router);
    }
}
