<?php

declare(strict_types = 1);

namespace Elie\Core\Router;

use Elie\Core\Router\Protocol\Path\Path;
use Elie\Core\Router\Protocol\Query\Query;

interface RouterConst
{

    public const PROTOCOL = 'protocol';

    public const NAMESPACE = 'namespace';

    public const ROUTE = 'route';

    public const ROUTES = 'routes';

    public const QUERY_CLASSNAME = Query::class;

    public const PATH_CLASSNAME = Path::class;

    public const PARAMS = 'params';

    public const CONTROLLER = 'controller';

    public const ACTION = 'action';

    public const PATH_INFO = 'PATH_INFO';

    public const REQUEST_URI = 'REQUEST_URI';

    public const SCRIPT_NAME = 'SCRIPT_NAME';
}
