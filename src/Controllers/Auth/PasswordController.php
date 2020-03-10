<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Auth\Auth;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
use Slim\Http\Request as SlimRequest;

class PasswordController extends Controller
{
    /** @var Auth */
    private $auth;

    /** @var ValidationRules */
    private $validationRules;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var PasswordValidation */
    private $passwordValidation;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->auth = $container->auth;
        $this->validationRules = $container->validationRules;
        $this->userRepository = $container->userRepository;
        $this->passwordValidation = $container->passwordValidation;
    }

    public function postChangePassword(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $data = ['password' => $user->password];
        
        $rules = $this->passwordValidation->getRules($data);
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
}
