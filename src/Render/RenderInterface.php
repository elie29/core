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
     *         'layout' => 'string: layout name. Default to layout',
     *         'views_path' => 'string: full path to the views folder',
     *         'cache_path' => 'string: full path to the cache folder',
     *         'cache_time' => 'int: default to -1 deactivated',
     *         'clean_output' => 'bool: clean the output. Default to false',
     *     ]
     * ]
     * </code>
     */
    public function __construct(ContainerInterface $container);

    /**
     * Clean the latest cached file.
     * This function must be called after a fetch, render
     * or hasExpired calls.
     * <code>
     *     // We clean the cached layout before expiration.
     *     if (! $this->hasExpired()) {
     *         $this->cleanCachedFile();
     *     }
     * </code>
     */
    public function cleanCachedFile(): void;

    /**
     * Assign data key/value to the rendering.
     *
     * @param array $data Data to be assigned
     *
     * @return self
     */
    public function assign(array $data);

    /**
     * Data would be rendered in json format.
     *
     * @return RenderInterface
     */
    public function jsonRendering();

    /**
     * Data would be rendered in text format.
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
     * Sets the current layout cache time.
     *
     * @param int $cachetime Layout cache time.
     *
     * @return RenderInterface
     */
    public function setLayoutCacheTime(int $cachetime);

    /**
     * Sets the current template(s) cache time.
     *
     * @param int $cachetime Template cache time.
     *
     * @return RenderInterface
     */
    public function setTemplateCacheTime(int $cachetime);

    /**
     * Verify if cache has expired.
     *
     * @param bool $isLayout Determines if we need to know that layout
     *     has expired or controller/action view.
     */
    public function hasExpired(bool $isLayout = true): bool;

    /**
     * Render a specific template by providing a template name.
     * Use cacheTemplateTime for specific template cache.
     * data arguments are overiden by assigned data!
     *
     * @param string $template Template name without extension eg. [products/care/item]
     * @param array  $data Data to be rendered.
     */
    public function fetchTemplate(array $data = [], $template = null): string;

    /**
     * If rendering is set to JSON we return JSON encode,
     * otherwise, we render the layout.
     * data arguments are overiden by assigned data!
     *
     * @param array $data Data to be rendered.
     */
    public function fetchLayout(array $data = []): string;
}
