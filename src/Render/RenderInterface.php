<?php

declare(strict_types = 1);

namespace Elie\Core\Render;

use Psr\Container\ContainerInterface;

interface RenderInterface
{

    /**
     * This service depneds on RouterInterface.
     *
     * This service has an optional dependency on the "config" container, which should
     * return an array. If found, it looks for the following structure core/render:
     * <code>
     * 'core' => [
     *     'render' => [
     *          // all keys are optional
     *         'layout' => 'string:layout name. Default to layout:could be folder/layout_name',
     *         'views_path' => 'string: full path to the views folder(layout/template)',
     *         'cache_path' => 'string: full path to the cache folder',
     *         'cache_time' => 'int: default to -1 deactivated.Cache time for layout only',
     *         'clean_output' => 'bool: clean the output. Default to false',
     *     ]
     * ]
     * </code>
     */
    public function __construct(ContainerInterface $container);

    /**
     * Data would be rendered in json format.
     * Cache will be disabled.
     *
     * @return RenderInterface
     */
    public function jsonRendering();

    /**
     * Data would be rendered in text format.
     * Cache will be disabled.
     *
     * @return RenderInterface
     */
    public function textRendering();

    /**
     * Data would be rendered in html format using layout/template views.
     *
     * @return RenderInterface
     */
    public function htmlRendering();

    /**
     * Tells to clean output.
     *
     * @param bool $cleanOutput True to clean output.
     *
     * @return RenderInterface
     */
    public function setCleanOutput(bool $cleanOutput);

    /**
     * Assign data key/value to the rendering.
     * Priority is given to this assigned data!
     *
     * @param array $data Data to be assigned for the whole layout/template.
     *
     * @return self
     */
    public function assign(array $data);

    /**
     * Change the current layout.
     *
     * @param string $layout layout_name or folder/layout_name under views_path.
     *
     * @return RenderInterface
     */
    public function changeLayout(string $layout);

    /**
     * Sets the current layout cache time.
     *
     * @param int $cachetime Layout cache time.
     *
     * @return RenderInterface
     */
    public function changeLayoutCacheTime(int $cachetime);

    /**
     * Verify if layout cache has expired.
     */
    public function hasLayoutExpired(): bool;

    /**
     * Verify if template cache has expired.
     *
     * @param string $cacheFile Cache filename for the current template.
     * @param int $cacheTime Cache time for the current template. Should be unique.
     */
    public function hasTemplateExpired(string $cacheFile, int $cacheTime): bool;

    /**
     * If rendering is set to JSON we return JSON encode,
     * otherwise, we render the layout.
     * data arguments are overiden by assigned data!
     *
     * @param array $data Specific data to be rendered for the current layout.
     */
    public function fetchLayout(array $data = []): string;

    /**
     * Template is rendered in html format.
     * Render a specific template by providing a template name.
     * data arguments are overiden by assigned data!
     *
     * @param array  $data specific data to be rendered for the given template.
     *  Not needed if template has not expired.
     * @param string $template Template name without extension eg. [products/care/item].
     *  if null, try controller/action template.
     * @param string $cacheFile Cache filename for the current template. Should be unique.
     * @param int $cacheTime Cache time for the current template.
     *
     * <code>
     *  $data = [];
     *  if ($render->hasTemplateExpired($cacheFile, $cacheTime)) {
     *    $data = ['fetch data from database'];
     *  }
     * </code>
     */
    public function fetchTemplate(
        array $data = [],
        $template = null,
        string $cacheFile = null,
        int $cacheTime = -1
    ): string;
}
