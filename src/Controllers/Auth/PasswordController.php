<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Validation\ValidationRules;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;

class PasswordController extends Controller
{
    public function postChangePassword(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $data = ['password' => $user->password];
        
        $rules = $this->getRules($data);
        $validation = $this->validator->validateRequest($request, $rules);
        
        if ($validation->failed()) {
            throw new ValidationException($validation->errors);
        }
        
        $password = $request->getParam('password');
        
        $user->password = Security::encodePassword($password);
        $user->save();
        
        $this->logger->info("Changed password for user: {$user}");
        
        return Response::json(
            $response,
            ['message' => $this->translate('Password change successful.')]
        );
    }
    
    /**
     * Get validation rules
     *
     * @param array $data
     * @return array
     */
    private function getRules(array $data) : array
    {
        $rules = new ValidationRules($this->container);

        return [
            'password_old' => v::matchesPassword($data['password']),
            'password' => $rules->get('password'),
        ];
    }
}
