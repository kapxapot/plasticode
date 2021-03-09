<?php

namespace Plasticode\Controllers;

use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\AppContext;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PasswordController extends Controller
{
    private AuthInterface $auth;
    private UserRepositoryInterface $userRepository;
    private PasswordValidation $passwordValidation;

    public function __construct(
        AppContext $appContext,
        AuthInterface $auth,
        UserRepositoryInterface $userRepository,
        PasswordValidation $passwordValidation
    )
    {
        parent::__construct($appContext);

        $this->auth = $auth;
        $this->userRepository = $userRepository;
        $this->passwordValidation = $passwordValidation;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $user = $this->auth->getUser();

        $data = ['password' => $user->password];

        $rules = $this->passwordValidation->getRules($data);
        $this->validate($request, $rules);

        $password = $request->getParsedBody()['password'] ?? null;

        $user->password = Security::encodePassword($password);

        $this->userRepository->save($user);

        $this->logger->info('Changed password for user: ' . $user);

        return Response::json(
            $response,
            ['message' => $this->translate('Password change successful.')]
        );
    }
}
