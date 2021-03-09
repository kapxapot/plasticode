<?php

namespace Plasticode\Validation\Interfaces;

use Plasticode\Validation\ValidationResult;
use Psr\Http\Message\ServerRequestInterface;

interface ValidatorInterface
{
    function validateArray(array $data, array $rules) : ValidationResult;
    function validateRequest(ServerRequestInterface $request, array $rules) : ValidationResult;
}
