<?php

namespace Hsharghi\MapAddress;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/map-address.php' => base_path('config/map-address.php'),
            ], 'config');
        }
        Nova::serving(function (ServingNova $event) {
            Nova::script('map-address-gmaps', $this->googleMapsSource());
            Nova::script('map-address', __DIR__.'/../dist/js/field.js');
            Nova::style('map-address', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/map-address.php', 'map-address');
    }


    private function googleMapsSource()
    {
        return vsprintf(
            'https://maps.googleapis.com/maps/api/js?key=%s&libraries=places&language=%s',
            [
                Config::get('map-address.api_key'),
                Config::get('map-address.language'),
            ]
        );
    }
}
