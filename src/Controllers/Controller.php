<?php

namespace Plasticode\Controllers;

use Plasticode\Collections\MenuCollection;
use Plasticode\Core\AppContext;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Util\Arrays;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Request as SlimRequest;
use Webmozart\Assert\Assert;

/**
 * Base controller for controllers showing views.
 */
abstract class Controller
{
    private AppContext $appContext;

    private SettingsProviderInterface $settingsProvider;
    private TranslatorInterface $translator;
    private ViewInterface $view;

    protected ValidatorInterface $validator;
    protected LoggerInterface $logger;

    protected bool $autoOneColumn = true;

    public function __construct(AppContext $appContext)
    {
        $this->appContext = $appContext;

        $this->settingsProvider = $appContext->settingsProvider();
        $this->translator = $appContext->translator();
        $this->view = $appContext->view();

        $this->validator = $appContext->validator();
        $this->logger = $appContext->logger();
    }

    /**
     * Get settings.
     *
     * @param mixed $default
     * @return mixed
     */
    protected function getSettings(string $path, $default = null)
    {
        return $this->settingsProvider->get($path, $default);
    }

    protected function buildParams(array $settings) : array
    {
        $params = [
            'menu' => $this->buildMenu($settings),
        ];

        $sidebar = $this->buildSidebar($settings);

        if (count($sidebar) > 0) {
            $params['sidebar'] = $sidebar;
        } elseif ($this->autoOneColumn === true) {
            $params['one_column'] = 1;
        }

        $params['rel_prev'] = Arrays::get($settings, 'params.paging.prev.url');
        $params['rel_next'] = Arrays::get($settings, 'params.paging.next.url');

        if (isset($settings['image'])) {
            $params['twitter_card_image'] = $settings['image'];
        }

        if (isset($settings['large_image'])) {
            $params['custom_card_image'] = $settings['large_image'];
        }

        $description = $settings['description'] ?? null;

        if (strlen($description) > 0) {
            $params['page_description'] = strip_tags($description);
        }

        $params['debug'] = $this->getSettings('debug');

        return array_merge($params, $settings['params']);
    }

    protected function buildMenu(array $settings) : MenuCollection
    {
        return $this->appContext->getMenus();
    }

    protected function buildSidebar(array $settings) : array
    {
        $result = [];

        $sidebarSettings = $settings['sidebar'] ?? [];
        
        foreach ($sidebarSettings as $part) {
            $result =
                $this->buildPart($settings, $result, $part) ??
                $this->buildActionPart($result, $part);
        }

        return $result;
    }

    protected function buildActionPart(array $result, string $part) : array
    {
        $bits = explode('.', $part);

        Assert::minCount($bits, 2, 'Unknown sidebar part: ' . $part);

        [$action, $entity] = $bits;

        Arrays::set($result, 'actions.' . $action . '.' . $entity, true);

        return $result;
    }

    /**
     * Builds sidebar part and adds it to result.
     * If the part is not built, returns null.
     */
    protected function buildPart(array $settings, array $result, string $part) : ?array
    {
        return null;
    }

    protected function translate(string $message) : string
    {
        return $this->translator->translate($message);
    }

    protected function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ) : ResponseInterface
    {
        return $this->view->render($response, $template, $data);
    }

    protected function validate(SlimRequest $request, array $rules) : void
    {
        $this
            ->validator
            ->validateRequest($request, $rules)
            ->throwOnFail();
    }
}
