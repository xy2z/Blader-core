<?php

namespace xy2z\Blader;

class Blader {

	/**
	 * Filename of the view to show on 404.
	 *
	 * @var string
	 */
	public $not_found_view;

	/**
	 * Variables that will be available in all templates.
	 *
	 * @var array
	 */
	public $global_vars = [];

	/**
	 * Views directory
	 *
	 * @var string
	 */
	public $views_dir = ROOT_DIR . '/views';

	/**
	 * Views cache directory
	 *
	 * @var string
	 */
	public $cache_dir = ROOT_DIR . '/cache';

	/**
	 * BladeOne mode (see BladeOne docs)
	 *
	 * @var integer
	 */
	public $blade_mode = BladeOneMarkdown::MODE_AUTO;

	/**
	 * Array of BladerRoute
	 *
	 * @var array
	 */
	private $routes = [];

	/**
	 * FastRoute disaptcher
	 *
	 * @var object
	 */
	private $dispatcher;

	/**
	 * The BladeOne object
	 *
	 * @var BladeOne
	 */
	private $blade;

	/**
	 * Render the page
	 *
	 */
	public function render() {
		// Templating
		$this->blade = new BladeOneMarkdown(
			$this->views_dir,
			$this->cache_dir,
			$this->blade_mode
		);

		// Routing
		$this->setRouter();

		// Render view
		$this->renderView();
	}

	/**
	 * Add a route
	 *
	 * @param string $method GET, POST, etc.
	 * @param string $route Matching
	 * @param string $view Filename in views dir.
	 * @param callable $callable (optional) Callback when route is hit. Eg. for setting content-type.
	 */
	public function addRoute(string $method, string $routePattern, string $view, $callable = null) {
		$route = new BladerRoute;
		$route->method = $method;
		$route->routePattern = $routePattern;
		$route->handler = [
			'view' => $view,
			'callable' => $callable
		];

		$this->routes[$routePattern] = $route;
	}

	/**
	 * Add routes to FastRoute
	 */
	private function setRouter() {
		$this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
			foreach ($this->routes as $row) {
				$r->addRoute($row->method, $row->routePattern, $row->handler);
			}
		});
	}

	/**
	 * Get current URI
	 *
	 * @return string URI
	 */
	private static function getUri() : string {
		// Fetch method and URI from somewhere
		$uri = $_SERVER['REQUEST_URI'];

		// Strip query string (?foo=bar) and decode URI
		if (false !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		return rawurldecode($uri);
	}

	/**
	 * Render view
	 *
	 */
	private function renderView() {
		$routeInfo = $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'], static::getUri());

		switch ($routeInfo[0]) {
			case \FastRoute\Dispatcher::NOT_FOUND:
				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
				echo $this->blade->run($this->not_found_view, $this->global_vars);
				break;

			case \FastRoute\Dispatcher::FOUND:
				$handler = $routeInfo[1];
				$template = $handler['view'];
				$callable = $handler['callable'];
				$template_data = array_merge($this->global_vars, $routeInfo[2]);

				if (is_callable($callable)) {
					$user_func_return = call_user_func($callable);

					if (is_array($user_func_return)) {
						$template_data = array_merge($template_data, $user_func_return);
					} else if (!empty($user_func_return)) {
						throw new \Exception('Return variable must be an array.');
					}
				}

				echo $this->blade->run($template, $template_data);
				break;
		}
	}
}
