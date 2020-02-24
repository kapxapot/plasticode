<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Auth\Auth;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
use Slim\Http\Request as SlimRequest;

/**
 * @property Auth $auth
 * @property UserRepositoryInterface $userRepository
 */
class PasswordController extends Controller
{
    public function postChangePassword(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $data = ['password' => $user->password];
        
        $rules = $this->getRules($data);
        $this->validate($request, $rules);
        
        $password = $request->getParam('password');
        
        $user->password = Security::encodePassword($password);

        $this->userRepository->save($user);
        
        $this->logger->info('Changed password for user: ' . $user);
        
        return Response::json(
            $response,
            ['message' => $this->translate('Password change successful.')]
        );
    }
    
    /**
     * Returns validation rules.
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
