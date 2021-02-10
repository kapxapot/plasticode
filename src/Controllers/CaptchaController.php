<?php

namespace Plasticode\Controllers;

use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Core\AppContext;
use Plasticode\Core\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CaptchaController extends Controller
{
    private CaptchaInterface $captcha;

    public function __construct(
        AppContext $appContext,
        CaptchaInterface $captcha
    )
    {
        parent::__construct($appContext);

        $this->captcha = $captcha;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $captcha = $this->captcha->generate(
            $this->getSettings('captcha_digits', 2),
            true
        );

        return Response::json(
            $response,
            ['captcha' => $captcha['captcha']]
        );
    }
}
