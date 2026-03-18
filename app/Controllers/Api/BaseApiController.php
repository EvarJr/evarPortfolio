<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * BaseApiController — app/Controllers/Api/BaseApiController.php
 * All API controllers extend this.
 * Auth is enforced via ['filter'=>'auth'] on each route in Routes.php
 */
abstract class BaseApiController extends BaseController {}
