<?php

namespace Common\Core\Exceptions;

use Illuminate\Auth\Access\Response as LaravelAccessResponse;

class AccessResponseWithAction extends LaravelAccessResponse
{
    public $action;
}
