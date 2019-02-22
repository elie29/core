<?php

declare(strict_types = 1);

namespace Elie\Core\Render;

use Elie\Core\Helper\Text;
use Elie\Core\Router\RouterInterface;
use Psr\Container\ContainerInterface;

/**
 * This class render a template (layouts or views).
 * All template must have phtml extension.
 */
class Render implements RenderInterface
{

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Layout name if needed. Default to layout.
     * It should be in views_path folder.
     *
     * @var string
     */
    protected $layout = RenderConst::LAYOUT;

    /**
     * Put the layout content in a cache file.
     * @var int
     * <code>
     *     -1 : cache is deactivated.
     *      0 : cache is with no time limit.
     *     >0 : cache is active until x seconds.
     * </code>
     */
    protected $cache_time = -1;

   /**
     * Put the template content in a cache file.
     * Useful in Ajax mode, when we render only the template.
     * @var int
     * <code>
     *     -1 : cache is deactivated.
     *      0 : cache is with no time limit.
     *     >0 : cache is active until x seconds.
     * </code>
     */
    protected $cache_template_time = -1;

    /**
     * Clean the output. Default to false.
     * @var bool
     */
    protected $clean_output = false;

    /**
     * The full path to the views folder.
     * defaults to the current dir.
     *
     * @var string
     */
    protected $views_path = '';

    /**
     * The full path to the cache folder.
     * defaults to the current dir.
     * @var string
     */
    protected $cache_path = '';

    /**
     * Rendering layout, text or json.
     * Default to Layout.
     * @var string
     */
    protected $rendering;

    /**
     * A global data variables.
     * Keys are not overriden.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Cached file.
     * @var string
     */
    private $cached_file = '';

    /**
     * Local data used in getLayout.
     * @var array
     */
    private $local_data = [];

    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get(RouterInterface::class);

        $config = $container->get('config');

        $params = $config['core']['render'] ?? [];

        $this->setParams($params);

        $this->htmlRendering();
    }

    public function cleanCachedFile(): void
    {
        if ($this->cached_file && file_exists($this->cached_file)) {
            @unlink($this->cached_file);
            $this->cached_file = '';
        }
    }

    public function assign(array $data): self
    {
        $this->data = $this->data ? array_merge($this->data, $data) : $data;
        return $this;
    }

    public function jsonRendering(): self
    {
        $this->rendering = RenderConst::JSON;
        return $this;
    }

    public function textRendering(): self
    {
        $this->rendering = RenderConst::TEXT;
        return $this;
    }

    public function htmlRendering(): self
    {
        $this->rendering = RenderConst::LAYOUT;
        return $this;
    }

    public function setCleanOutput(bool $cleanOutput): self
    {
        $this->clean_output = $cleanOutput;
        return $this;
    }

    public function setLayoutCacheTime(int $cachetime): self
    {
        $this->cache_time = $cachetime;
        return $this;
    }

    public function setTemplateCacheTime(int $cachetime): self
    {
        $this->cache_template_time = $cachetime;
        return $this;
    }

    public function hasExpired(bool $isLayout = true): bool
    {
        // Empty the cached file
        $this->cached_file = '';

        // Test cached time
        $cacheTime = $isLayout ? $this->cache_time : $this->cache_template_time;

        if ($cacheTime < 0) {
            // Cache is not used.
            return true;
        }

        // We set the cached file for later use.
        $this->setCachedFile($isLayout);

        if (! file_exists($this->cached_file)) {
            return true;
        }

        return $cacheTime > 0 && $cacheTime < (time() - filemtime($this->cached_file));
    }

    public function fetchTemplate(array $data = [], $template = null): string
    {
        if (null === $template) {
            // template should be under controller/action.phtml
            $template = $this->router->getController() . '/' . $this->router->getAction();
        }

        return $this->fetch($data, $template, false);
    }

    public function fetchLayout(array $data = []): string
    {
        return $this->fetch($data, $this->layout, true);
    }

    /**
     * Gives the ability to use $this->var in template.
     *
     * @param string $name Variable name.
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->local_data[$name])) {
            return $this->local_data[$name];
        }
        // Development error
        return "{$name} is not set correctly";
    }

    /**
     * Determines if local variables is set.
     *
     * @param string $name Local variable name.
     */
    public function __isset($name): bool
    {
        return isset($this->local_data[$name]);
    }

    protected function setParams(array $params): void
    {
        if (isset($params[RenderConst::LAYOUT])) {
            $this->layout = $params[RenderConst::LAYOUT];
        }

        $viewsPath = $params[RenderConst::VIEWS_PATH] ?? __DIR__;
        $cachePath = $params[RenderConst::CACHE_PATH] ?? __DIR__;

        if (isset($params[RenderConst::CACHE_TIME])) {
            $this->setLayoutCacheTime($params[RenderConst::CACHE_TIME]);
        }

        if (isset($params[RenderConst::CLEAN_OUTPUT])) {
            $this->setCleanOutput($params[RenderConst::CLEAN_OUTPUT]);
        }

        // Clean path
        $this->views_path = $this->clean($viewsPath);
        $this->cache_path = $this->clean($cachePath);
    }

    /**
     * fetch the data depending on rendering mode.
     *
     * @param array  $data     Data to be rendered.
     * @param string $view     The view to be setteled.
     * @param bool   $isLayout Test if we settle a layout or a template.
     *
     * @throws RenderException
     */
    protected function fetch(array $data, $view, $isLayout): string
    {
        // Priority is to assigned data
        if ($this->data) {
            $data = array_merge($data, $this->data);
        }

        if ($this->rendering === RenderConst::JSON) {
            return json_encode($data);
        }

        if ($this->rendering === RenderConst::TEXT) {
            return implode("\n", $data);
        }

        return $this->getLayoutContent($data, $view, $isLayout);
    }

    /**
     * Render the provided view or get it from cache.
     *
     * @param array  $data     Data to be rendered.
     * @param string $view     The view to be setteled.
     * @param bool   $isLayout Test if we settle a layout or a template.
     *
     * @throws RenderException
     */
    protected function getLayoutContent(array $data, $view, $isLayout): string
    {
        // Define views script extension
        $view = $this->views_path . $view . '.phtml';

        // Check view script file presence
        if (! file_exists($view)) {
            throw new RenderException('Can\'t find view script ' . $view);
        }

        // If content has not expired
        if (! $this->hasExpired($isLayout)) {
            return file_get_contents($this->cached_file);
        }

        // Save local data
        $this->local_data = $data;

        // Start ouput buffering
        ob_start();

        // include() instead of include_once() allows for multiple views with the same name
        include $view;

        // Set to empty
        $this->local_data = [];

        // Get output buffer content, then clear it
        $output = ob_get_clean();

        if ($this->clean_output) {
            $output = Text::cleanContent($output);
        }

        // Write result if necessary
        if ($this->cached_file) {
            file_put_contents($this->cached_file, $output, LOCK_EX);
        }

        // Return output
        return $output;
    }

    /**
     * Set the cached file properly.
     *
     * @param bool $isLayout Determines if we are working on layout or view.
     */
    private function setCachedFile($isLayout): void
    {
        // Create the cached_file: if no cache path, we take views path
        $path = $this->cache_path ? $this->cache_path : $this->views_path;

        if ($isLayout) {
            $this->cached_file = $this->layout . '.';
        }

        // We add controller and action
        $this->cached_file .=
            $this->router->getController() . '.' .
            $this->router->getAction() .
            $this->router->getImplodedParams();

        // Replace directory separator
        $this->cached_file = $path . str_replace(['/', '\\'], '.', $this->cached_file);
    }

    /**
     * Add a directory sperator at the end.
     *
     * @param string $path Path name.
     */
    private function clean(string $path): string
    {
        return $path ? rtrim($path, '/\\') . '/' : '';
    }
}
