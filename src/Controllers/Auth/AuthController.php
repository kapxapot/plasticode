<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Services\AuthService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Webmozart\Assert\Assert;

class AuthController extends Controller
{
    private AuthService $authService;
    private CaptchaInterface $captcha;
    private UserRepositoryInterface $userRepository;
    private UserValidation $userValidation;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);

        $this->authService = $container->authService;
        $this->captcha = $container->captcha;
        $this->userRepository = $container->userRepository;
        $this->userValidation = $container->userValidation;
    }

    public function postSignUp(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
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
        $token = $this->authService->attempt($user->login, $password);
        
        $this->logger->info('User signed up: ' . $user);

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

    public function postSignIn(
        Request $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $token = $this->authService->attempt(
            $request->getParam('login'),
            $request->getParam('password')
        );
        
        if (!$token) {
            throw new AuthenticationException('Incorrect user/password.');
        }

        $user = $token->user();

        $this->logger->info('User logged in: ' . $user);

        Assert::notNull($token);

        return Response::json(
            $response,
            [
                'token' => $token->token,
                'message' => $this->translate('Login successful.'),
            ]
        );
    }

    public function postSignOut(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $this->authService->logout();
        return $response;
    }
}
