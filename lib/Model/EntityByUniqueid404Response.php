<?php

namespace Flyo\Model;

/**
 * EntityByUniqueid404Response Class Doc Comment
 *
 * @category Class
 * @description The body of the 404 both entity endpoints answer with. Carries a recovery hint when the identifier was an expired or revoked **draft link** token.
 * @package  Flyo
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 *
 * @deprecated since 3.4, use {@see EntityNotFoundResponse} instead. The schema was renamed
 *             from `entityByUniqueid_404_response` to `entityNotFoundResponse` because both
 *             entity endpoints share it. This class is kept as a subclass of the new model so
 *             existing code keeps loading and type-checking, and will be removed in a future
 *             major release.
 */
class EntityByUniqueid404Response extends EntityNotFoundResponse
{
    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'entityByUniqueid_404_response';

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }
}
