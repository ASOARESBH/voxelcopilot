<?php
/**
 * VOXEL Copilot — Front Controller
 */
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;

require_once BASE_PATH . '/routes/web.php';

Router::dispatch();
