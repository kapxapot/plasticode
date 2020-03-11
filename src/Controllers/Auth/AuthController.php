<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Auth\Auth;
use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;
use Webmozart\Assert\Assert;

class AuthController extends Controller
{
    /** @var Auth */
    private $auth;

    /** @var CaptchaInterface */
    private $captcha;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserValidation */
    private $userValidation;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->auth = $container->auth;
        $this->captcha = $container->captcha;
        $this->userRepository = $container->userRepository;
        $this->userValidation = $container->userValidation;
    }

    public function postSignUp(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data = $request->getParsedBody();

        $rules = $this->userValidation->getRules($data);
        $this->validate($request, $rules);

        if (!$this->captcha->validate($data['captcha'])) {
            throw new AuthenticationException('Incorrect or expired captcha.');
        }

        unset($data['captcha']);

        $user = $this->userRepository->create($data);

        $password = $user->password;
        $user->password = Security::encodePassword($password);

        $this->userRepository->save($user);

        // signing in
        $user = $this->auth->attempt($user->login, $password);
        
        $this->logger->info('User signed up: ' . $user);

        $token = $this->auth->getToken();
        $response = $response->withStatus(201);

        $response = Response::json(
            $response,
            [
                'token' => $token->token,
                'message' => $this->translate('Registration successful.'),
            ]
        );

        return $response;
    }

    public function postSignIn(SlimRequest $request, ResponseInterface $response) : ResponseInterface
    {
        $user = $this->auth->attempt(
            $request->getParam('login'),
            $request->getParam('password')
        );
        
        if (!$user) {
            throw new AuthenticationException('Incorrect user/password.');
        }

        $this->logger->info('User logged in: ' . $user);
    
        $token = $this->auth->getToken();

        Assert::notNull($token);

        return Response::json(
            $response,
            [
                'token' => $token->token,
                'message' => $this->translate('Login successful.'),
            ]
        );
    }

    public function postSignOut(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $this->auth->logout();
        return $response;
    }
}
