<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    \Wanphp\Plugins\About\Domain\AboutInterface::class => \DI\autowire(\Wanphp\Plugins\About\Repositories\AboutRepository::class)
  ]);
};
