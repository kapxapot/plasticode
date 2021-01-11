<?php

namespace Plasticode\Settings\Transformations;

use Plasticode\Interfaces\ArrayTransformationInterface;

/**
 * Adds web root, api and auth token key.
 */
class WebTransformation implements ArrayTransformationInterface
{
    public function transformArray(array $data): array
    {
        $root = $data['folders']['root'];
        $data['root'] = $root;

        $data['api'] = $root . ($data['folders']['api'] ?? '/api/v1/');

        $rootSafe = str_replace('/', '', $root);

        if (strlen($rootSafe) > 0) {
            $data['auth_token_key'] = $rootSafe . '_auth_token';
        }

        return $data;
    }
}
