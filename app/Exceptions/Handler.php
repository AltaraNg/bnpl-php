<?php

namespace App\Exceptions;



use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\Exception\TransportException;
use Throwable;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use App\Traits\ApiResponseTrait as TraitsApiResponseTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use TraitsApiResponseTrait;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        //        if ($request->expectsJson()) {
        if ($e instanceof PostTooLargeException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => "Size of attached file should be less " . ini_get("upload_max_filesize") . "B"
                ],
                400
            );
        }
        if ($e instanceof AuthenticationException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Unauthenticated or Token Expired, Please Login'
                ],
                401
            );
        }
        if ($e instanceof ThrottleRequestsException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Too Many Requests,Please Slow Down'
                ],
                429
            );
        }
        if ($e instanceof ModelNotFoundException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => class_basename($e->getModel()) . ' not found'
                ],
                404
            );
        }
        if ($e instanceof ValidationException) {

            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ],
                422
            );
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ],
                405
            );
        }
        if ($e instanceof QueryException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'There was Issue with the Query',
                    'exception' => $e

                ],
                500
            );
        }
        if ($e instanceof TransportException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Connection could not be established with mail host',
                    'exception' => $e
                ],
                500
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Route not found',
                    'exception' => $e
                ],
                500
            );
        }

        if ($e instanceof AuthorizationException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ],
                403
            );
        }
        if ($e instanceof InvalidArgumentException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ],
                500
            );
        }

        if ($e instanceof BadRequestHttpException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ],
                400
            );
        }
        if ($e instanceof \Exception) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'exception' => $e
                ],
                500
            );
        }
        if ($e instanceof \Error) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => "There was some internal error",
                    'exception' => $e
                ],
                500
            );
        }

        return parent::render($request, $e);
    }
}
