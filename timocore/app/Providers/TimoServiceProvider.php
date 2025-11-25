<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Builder;

class TimoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        Blade::if('role', function (...$roles) {
            $user = auth()->user();

            if (!$user)
                return false;

            $flattened = [];

            foreach ($roles as $role) {
                $parts = explode('|', $role);

                $parts = array_map('trim', $parts);

                $flattened = array_merge($flattened, $parts);
            }

            return $user->hasAnyRoleName(...$flattened);
        });

        Builder::macro('mine', function () {
            $user = auth()->user();

            if (!$user || !$user->isStaff()) {
                return $this;
            }

            return $this->where($this->getModel()->getTable() . '.user_id', $user->id);
        });

        Relation::macro('mine', function () {
            return $this->getQuery()->mine();
        });

        /*** TIMEZONE STUFFS */
        Builder::macro('whereDateOrg', function ($column, $date = null, $tz = null) {
            $tz = $tz ?? orgTimezone();      
            $stz = config('app.timezone');
            if ($date) {
                if ($date instanceof Carbon) {
                    $date = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $date->format('Y-m-d H:i:s'),
                        $tz
                    );
                } else {
                    $date = Carbon::parse($date, $tz);
                }
            }else{
                $date = now($tz);
            }
            $date = $date ? Carbon::parse($date, $tz) : now($tz);
            $start = $date->copy()->startOfDay(); 
            $end   = $date->copy()->endOfDay();
        
            $startUtc = $start->copy()->setTimezone($stz);
            $endUtc   = $end->copy()->setTimezone($stz);
            return $this->whereBetween($column, [$startUtc, $endUtc]);
        });
        
        Relation::macro('whereDateOrg', function ($column, $date = null, $tz = null) {
            return $this->getQuery()->whereDateOrg($column, $date, $tz);
        });

        
        Builder::macro('whereBetweenOrg', function ($column, $start, $end, $tz = null) {
            $tz = $tz ?? orgTimezone();
            $stz = config('app.timezone');
            // Normalize START as local org time (wall time)
            if ($start instanceof Carbon) {
                $start = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $start->format('Y-m-d H:i:s'),
                    $tz
                );
            } else {
                $start = Carbon::parse($start, $tz);
            }

            // Normalize END as local org time (wall time)
            if ($end instanceof Carbon) {
                $end = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $end->format('Y-m-d H:i:s'),
                    $tz
                );
            } else {
                $end = Carbon::parse($end, $tz);
            }

            $startUtc = $start->copy()->setTimezone($stz);
            $endUtc = $end->copy()->setTimezone($stz);

            return $this->whereBetween($column, [$startUtc, $endUtc]);
        });

        Relation::macro('whereBetweenOrg', function ($column, $start, $end, $tz = null) {
            return $this->getQuery()->whereBetweenOrg($column, $start, $end, $tz);
        });

        Builder::macro('forOrgDayRange', function ($startDate, $endDate, $column = 'started_at', $tz = null) {
            $tz = $tz ?? orgTimezone();

            $start = Carbon::parse($startDate, $tz)->startOfDay();
            $end = Carbon::parse($endDate, $tz)->endOfDay();

            return $this->whereBetweenOrg($column, $start, $end, $tz);
        });

        Relation::macro('forOrgDayRange', function ($startDate, $endDate, $column = 'started_at', $tz = null) {
            return $this->getQuery()->forOrgDayRange($startDate, $endDate, $column, $tz);
        });

        // Timezone-aware DATE helpers for SELECTs
        Builder::macro('selectDateTz', function (
            $column = 'started_at',
            $alias = 'd',
            $fromOffset = null,
            $toOffset = null
        ) {
            $fromOffset = $fromOffset ?? now(config('app.timezone'))->format('P');
            $toOffset = $toOffset ?? now(orgTimezone())->format('P');

            return $this->selectRaw(
                "DATE(CONVERT_TZ($column, '$fromOffset', '$toOffset')) as $alias"
            );
        });

        Relation::macro('selectDateTz', function (
            $column = 'started_at',
            $alias = 'd',
            $fromOffset = null,
            $toOffset = null
        ) {
            return $this->getQuery()->selectDateTz($column, $alias, $fromOffset, $toOffset);
        });

        // Timezone-aware DATE_FORMAT helper for SELECTs
        Builder::macro('selectTzFormat', function (
            $column = 'started_at',
            $format = '%Y-%m-%d',
            $alias = 'formatted_on',
            $fromOffset = null,
            $toOffset = null
        ) {
            $fromOffset = $fromOffset ?? now(config('app.timezone'))->format('P');
            $toOffset = $toOffset ?? now(orgTimezone())->format('P');

            return $this->selectRaw(
                "DATE_FORMAT(CONVERT_TZ($column, '$fromOffset', '$toOffset'), '$format') AS $alias"
            );
        });

        Relation::macro('selectTzFormat', function (
            $column = 'started_at',
            $format = '%Y-%m-%d',
            $alias = 'formatted_on',
            $fromOffset = null,
            $toOffset = null
        ) {
            return $this->getQuery()->selectTzFormat($column, $format, $alias, $fromOffset, $toOffset);
        });
    }
}
