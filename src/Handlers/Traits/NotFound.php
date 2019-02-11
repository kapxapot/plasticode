<?php

namespace Plasticode\Handlers\Traits;

trait NotFound
{
	public function __invoke($request, $response)
	{
		$params = $this->buildParams([
			'params' => [
				'text' => $this->translate('Page not found or moved somewhere we don\'t know where.'),
				'title' => $this->translate('Error 404'),
				'no_breadcrumbs' => true,
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/generic.twig', $params)
			->withStatus(404);
	}
}
