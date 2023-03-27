<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAccessMiddleware
{
    use ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {

        if($request->header('bnlp-admin-access') !== env('BNLP_ADMIN_ACCESS')){
            return  $this->respondForbidden('Invalid access token provided');
        }

        if ($request->header('bnlp-admin-access-auth-user-id') == null) {
            Log::info('The currently authenticated user id is not supplied');
        } else {
            $userID = $request->header('bnlp-admin-access-auth-user-id');
            Auth::loginUsingId($userID);

        }
        
        return $next($request);
    }
}
