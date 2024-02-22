<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Corytech\BigNumber\Serializer\Normalizer\BigNumberNormalizer;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
            ->autoconfigure()
        ->set(BigNumberNormalizer::class, BigNumberNormalizer::class)
    ;
};
