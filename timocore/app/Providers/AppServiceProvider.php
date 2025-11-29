<?php

namespace App\Providers;

use App\Models\User;
use App\Lib\Searchable;
use App\Constants\Status;
use App\Models\UserNotification;
use App\Models\AdminNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SupportTicket;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::mixin(new Searchable);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $viewShare['emptyMessage'] = 'Data not found';
        view()->share($viewShare);


        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'bannedUsersCount'           => User::banned()->count(),
                'emailUnverifiedUsersCount' => User::emailUnverified()->count(),
                'pendingTicketCount'         => SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count(),
                'updateAvailable'    => version_compare(gs('available_version'),systemDetails()['version'],'>') ? 'v'.gs('available_version') : false,
            ]);
        });

        view()->composer('Template::partials.sidebar', function ($view) {
            $view->with([
                'pendingMemberCount' => User::where('status', Status::USER_PENDING)->where('organization_id', organizationId())->count(),
            ]);
        });

        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotifications' => AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get(),
                'adminNotificationCount' => AdminNotification::where('is_read', Status::NO)->count(),
            ]);
        });

        view()->composer('Template::partials.header', function ($view) {
            $view->with([
                'userNotifications' => UserNotification::where('is_read', Status::NO)->where('user_id', auth()->id())->with('sender')->orderBy('id', 'desc')->take(10)->get(),
                'userNotificationCount' => UserNotification::where('is_read', Status::NO)->where('user_id', auth()->id())->count(),
            ]);
        });

        if (gs('force_ssl')) {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
    }
}
