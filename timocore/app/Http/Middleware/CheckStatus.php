<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = auth()->user();
            if ($user->status) {

                // organization suspend checking
                if($user->organization?->is_suspend) {
                    if($user->has_organization && in_array(request()->route()->getName(), $this->suspendedAllowRoutes())) {
                        return $next($request);
                    } elseif($user->has_organization) {
                        return to_route('user.invoice.list');
                    }

                    return to_route('user.suspended');
                }


                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'Your account is banned, please contact to your organization owner';
                    return response()->json([
                        'remark'=>'banned',
                        'status'=>'error',
                        'message'=>['error'=>$notify],
                        'data'=>[
                            'user'=>$user
                        ],
                    ]);
                }else{
                    return to_route('user.authorization');
                }
            }
        }
        abort(403);
    }

    private function suspendedAllowRoutes() {
        return [
            'user.invoice.list',
            'user.invoice.download',
            'user.invoice.pay',
            'user.deposit.insert',
            'user.deposit.quick',
            'user.deposit.confirm',
            'user.deposit.history',
            'user.transactions',
        ];
    }
}
