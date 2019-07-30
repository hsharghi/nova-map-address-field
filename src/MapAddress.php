<?php

namespace Hsharghi\MapAddress;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MapAddress extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'map-address';
    public $latField = 'lat';
    public $lngField = 'lng';

    public function latField($fieldName)
    {
        $this->latField = $fieldName;
    }

    public function lngField($fieldName)
    {
        $this->lngField = $fieldName;
    }


    public function location($lat, $lng)
    {
        return $this->withMeta([
            'lat' => $lat,
            'lng' => $lng
        ]);
    }

    public function fillInto(NovaRequest $request, $model, $attribute, $requestAttribute = null)
    {
        parent::fillAttributeFromRequest($request, $requestAttribute ?? $this->attribute, $model, $attribute);
        parent::fillAttributeFromRequest($request, 'lat', $model, $this->latField);
        parent::fillAttributeFromRequest($request, 'lng', $model, $this->lngField);
    }
}
