<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Serializer\Normalizer;

use Corytech\BigNumber\BigNumber;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BigNumberNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === BigNumber::class;
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = []): BigNumber
    {
        try {
            return BigNumber::of($data);
        } catch (\DomainException|\TypeError $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                'This value should be numeric',
                $data,
                [Type::BUILTIN_TYPE_STRING, Type::BUILTIN_TYPE_FLOAT, Type::BUILTIN_TYPE_INT],
                $context['deserialization_path'] ?? null,
                true
            );
        }
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof BigNumber;
    }

    public function normalize($object, ?string $format = null, array $context = []): string
    {
        /** @var BigNumber $object */
        return $object->shorten();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BigNumber::class => true,
        ];
    }
}
