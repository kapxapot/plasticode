<?php

namespace Plasticode\Validation\Interfaces;

use Plasticode\Validation\ValidationResult;
use Slim\Http\Request;

interface ValidatorInterface
{
    function validateArray(array $data, array $rules) : ValidationResult;
    function validateRequest(Request $request, array $rules) : ValidationResult;
}
