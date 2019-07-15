## Nova Map Address Field

A Nova field to set address via marker on map and save both the reverse geocoded coordinates and lat/lng value of the marker
## Installation

You can install the package in to a Laravel app that uses Nova via composer:

```bash
composer require hsharghi/nova-map-address-field
```

## Configuration
Publish the package config file:
```bash
php artisan vendor:publish --provider="SoftWorks\MapAddress\FieldServiceProvider"
```

This is the contents of the file which will be published at [config/map-address.php](config/map-address.php).

Add the following keys to your `.env`:

```env
MAP_ADDRESS_API_KEY=xxx
MAP_ADDRESS_LANGUAGE=en
```

Don't have Google Maps API key? Get it from here: https://console.developers.google.com

## Usage:
Add the below to Nova/User.php resource:

In additin to `address` field in Nova resource model, both `lat` and `lng` fields should exists on model. 

```php

MapAddress::make('address'),

// Set the initial map location. 
MapAddress::make('address')
    ->location(40.61512700, -99.24207500),

// Set custom field name for `lat` and `lng` fields
MapAddress::make('address')
    ->latField('latitude')
    ->lngField('longitude'),

```


## Support:
hsharghi@icloud.com


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
