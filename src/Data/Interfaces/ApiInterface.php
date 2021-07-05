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
    public function get(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Returns many entities.
     */
    public function getMany(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGeneratorInterface $generator,
        array $options = []
    ): ResponseInterface;

    /**
     * Creates entity.
     */
    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Updates entity.
     */
    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;

    /**
     * Deletes entity.
     */
    public function delete(
        ResponseInterface $response,
        int $id,
        EntityGeneratorInterface $generator
    ): ResponseInterface;
}
