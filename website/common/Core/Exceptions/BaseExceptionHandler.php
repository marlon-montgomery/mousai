<?php

namespace Common\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Throwable;

class BaseExceptionHandler extends Handler
{
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception) && config('app.env') === 'production') {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $array = parent::convertExceptionToArray($e);
        $previous = $e->getPrevious();

        if ($previous && method_exists($previous, 'response') && property_exists($previous->response(), 'action')) {
            $array['action'] = $e->getPrevious()->response()->action;
        }

        if ($array['message'] === 'Server Error') {
            $array['message'] = __('There was an issue. Please try again later.');
        }

        return $array;
    }
}
