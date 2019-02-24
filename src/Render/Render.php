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
    private $layout_cache_file = '';

    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get(RouterInterface::class);

        $config = $container->get('config');

        $params = $config['core']['render'] ?? [];

        $this->setParams($params);

        $this->htmlRendering();
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

    public function assign(array $data): self
    {
        $this->data = $this->data ? array_merge($this->data, $data) : $data;
        return $this;
    }

    public function changeLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function changeLayoutCacheTime(int $cachetime): self
    {
        $this->cache_time = $cachetime;
        return $this;
    }

    public function hasLayoutExpired(): bool
    {
        // Empty the cached file
        $this->layout_cache_file = '';

        if ($this->cache_time < 0) {
            // Cache is not used.
            return true;
        }

        $layoutName = $this->layout . '.' .
            $this->router->getController() . '.' .
            $this->router->getAction() .
            $this->router->getImplodedParams();

        // We set the layout cached file for later use.
        $this->layout_cache_file = $this->cache_path . str_replace(['/', '\\'], '-', $layoutName);

        return $this->hasCacheExpired($this->layout_cache_file, $this->cache_time);
    }

    public function hasTemplateExpired(string $cacheFile, int $cacheTime): bool
    {
        if ($cacheTime < 0) {
            // Cache is not used.
            return true;
        }

        return $this->hasCacheExpired($this->cache_path . $cacheFile, $cacheTime);
    }

    public function fetchLayout(array $data = []): string
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

        return $this->getHtmlContent($data);
    }

    public function fetchTemplate(
        array $data = [],
        $template = null,
        string $cacheFile = null,
        int $cacheTime = -1
    ): string {

        // If content has not expired
        if ($cacheFile && ! $this->hasTemplateExpired($cacheFile, $cacheTime)) {
            return file_get_contents($this->cache_path . $cacheFile);
        }

        if (null === $template) {
            // View should be under controller/action.phtml
            $template = $this->router->getController() . '/' .  $this->router->getAction();
        }

        $output = $this->getViewContent($template, $data);

        // Write result if necessary
        if ($cacheFile && $cacheTime >= 0) {
            file_put_contents($this->cache_path . $cacheFile, $output, LOCK_EX);
        }

        // Return output
        return $output;
    }

    /**
     * Render the provided view or get it from cache.
     *
     * @param array $data Data to be rendered.
     *
     * @throws RenderException
     */
    protected function getHtmlContent(array $data): string
    {
        // If content has not expired
        if (! $this->hasLayoutExpired()) {
            return file_get_contents($this->layout_cache_file);
        }

        $output = $this->getViewContent($this->layout, $data);

        // Write result if necessary
        if ($this->cache_time >= 0) {
            file_put_contents($this->layout_cache_file, $output, LOCK_EX);
        }

        // Return output
        return $output;
    }

    protected function getViewContent(string $view, array $data): string
    {
        // Define views script extension
        $view = $this->views_path . $view . '.phtml';

        // Check view script file presence
        if (! file_exists($view)) {
            throw new RenderException("Can't find view script  {$view}");
        }

        // Start ouput buffering
        ob_start();

        coreIncludeView($view, $data);

        // Get output buffer content, then clear it
        $output = ob_get_clean();

        if ($this->clean_output) {
            $output = Text::cleanContent($output);
        }

        return $output;
    }

    protected function setParams(array $params): void
    {
        if (isset($params[RenderConst::LAYOUT])) {
            $this->layout = $params[RenderConst::LAYOUT];
        }

        $viewsPath = $params[RenderConst::VIEWS_PATH] ?? __DIR__;
        $cachePath = $params[RenderConst::CACHE_PATH] ?? __DIR__;

        if (isset($params[RenderConst::CACHE_TIME])) {
            $this->changeLayoutCacheTime($params[RenderConst::CACHE_TIME]);
        }

        if (isset($params[RenderConst::CLEAN_OUTPUT])) {
            $this->setCleanOutput($params[RenderConst::CLEAN_OUTPUT]);
        }

        // Clean path
        $this->views_path = $this->clean($viewsPath);
        $this->cache_path = $this->clean($cachePath);
    }

    /**
     * Add a directory sperator at the end.
     *
     * @param string $path Path name.
     */
    protected function clean(string $path): string
    {
        return $path ? rtrim($path, '/\\') . '/' : '';
    }

    protected function hasCacheExpired(string $cacheFile, int $cacheTime): bool
    {
        if (! file_exists($cacheFile)) {
            // file does not exist
            return true;
        }

        return $cacheTime > 0 && $cacheTime < (time() - filemtime($cacheFile));
    }
}

/**
 * include() instead of include_once() allows for multiple views with the same name
 * isolate content
 */
function coreIncludeView($view, array $data)
{
    extract($data);

    include $view;
}
