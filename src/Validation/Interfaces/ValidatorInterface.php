<?php

namespace Plasticode\Validation\Interfaces;

use Plasticode\Validation\ValidationResult;
use Slim\Http\Request;

interface ValidatorInterface
{
    public function validateArray(array $data, array $rules) : ValidationResult;
    public function validateRequest(Request $request, array $rules) : ValidationResult;
}
