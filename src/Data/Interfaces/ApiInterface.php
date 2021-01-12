<?php

namespace Plasticode\Data\Interfaces;

use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ApiInterface
{
    /**
     * Returns entity by id.
     */
    function get(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Returns many entities.
     */
    function getMany(
        ResponseInterface $response,
        EntityGeneratorInterface $generator,
        array $options = []
    ): ResponseInterface;

    /**
     * Creates entity.
     */
    function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Updates entity.
     */
    function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Deletes entity.
     */
    function delete(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;
}
