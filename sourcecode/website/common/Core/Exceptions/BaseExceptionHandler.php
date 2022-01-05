<?php

namespace Common\Core\Exceptions;

use ErrorException;
use Illuminate\Foundation\Exceptions\Handler;
use Str;
use Throwable;

class BaseExceptionHandler extends Handler
{
    public function register()
    {
        $this->renderable(function (ErrorException $e) {
            //if (config('app.env') !== 'production') return;

            if (
                Str::contains(
                    $e->getMessage(),
                    ['failed to open stream: Permission denied', 'mkdir(): Permission denied']
                )
            ) {
                return $this->filePermissionResponse($e);
            }
        });
    }

    public function report(Throwable $e)
    {
        if (
            app()->bound('sentry') &&
            $this->shouldReport($e) &&
            config('app.env') === 'production'
        ) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $array = parent::convertExceptionToArray($e);
        $previous = $e->getPrevious();

        if (
            $previous &&
            method_exists($previous, 'response') &&
            property_exists($previous->response(), 'action')
        ) {
            $array['action'] = $e->getPrevious()->response()->action;
        }

        if ($array['message'] === 'Server Error') {
            $array['message'] = __(
                'There was an issue. Please try again later.',
            );
        }

        return $array;
    }

    protected function filePermissionResponse(ErrorException $e)
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => 'test', 'action' => 'yolo']);
        } else {
            preg_match('/\((.+?)\):/', $e->getMessage(), $matches);
            $path = $matches[1] ?? null;
            // should not return a view here, in case laravel views folder is not readable as well
            return response(
                "<div style='text-align:center'><h1>Could not access a file or folder</h1> <br> Location: <b>$path</b><br>" .
                '<p>See the article here for possible solutions: <a target="_blank" href="https://support.vebto.com/help-center/articles/21/25/207/changing-file-permissions">https://support.vebto.com/help-center/articles/207/changing-file-permissions</a></p></div>',
            );
        }
    }
}
