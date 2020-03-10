<?php

namespace Plasticode\Controllers\Auth;

use Plasticode\Auth\Auth;
use Plasticode\Controllers\Controller;
use Plasticode\Core\Response;
use Plasticode\Core\Security;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator as v;
use Slim\Http\Request as SlimRequest;

/**
 * @property Auth $auth
 * @property UserRepositoryInterface $userRepository
 */
class PasswordController extends Controller
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var Auth */
    private $auth;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->settingsProvider = $container->settingsProvider;
        $this->auth = $container->auth;
        $this->userRepository = $container->userRepository;
    }

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
        $rules = new ValidationRules($this->settingsProvider);

        return [
            'password_old' => v::matchesPassword($data['password']),
            'password' => $rules->get('password'),
        ];
    }
}
