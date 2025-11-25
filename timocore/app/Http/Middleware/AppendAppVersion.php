<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendAppVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (method_exists($response, 'getData')) {
            $data = $response->getData(true);
            $data['app_version'] = gs('app_version');
            $data['screenshot_alert'] = boolval(gs('screenshot_alert'));


            if(auth()->check()) {
                $data['suspended'] = (bool)auth()->user()->organization->is_suspend;
                $data['tracking_status'] = (bool)auth()->user()->tracking_status;
                $data['tasks_ids'] = auth()->user()->tasks()->pluck('tasks.id')->toArray();
                $data['project_ids'] = auth()->user()->projects()->pluck('projects.id')->toArray();
            }



            $response->setData($data);
        }

        return $response;
    }
}
