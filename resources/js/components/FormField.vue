<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div class="google-map" :id="mapName"></div>
            <br>
            <input
                    :id="field.name"
                    type="text"
                    class="w-full form-control form-input form-input-bordered"
                    :class="errorClasses"
                    :placeholder="field.name"
                    v-model="value"
                    ref="aca"
            />
        </template>
    </default-field>
</template>

<style scoped>
    .google-map {
        width: 700px;
        height: 300px;
        margin: 0 auto;
        background: gray;
        border: solid 1px #ccc;
    }
</style>

<script>
    import {FormField, HandlesValidationErrors} from 'laravel-nova'

    export default {
        name: 'google-map',

        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                lat: 0,
                lng: 0,
                mapName: this.name + "-map",
                map: null,
                places: null,
                autocomplete: null,
                countryRestrict: {'country': 'us'},
                countries:
                    {
                        'ca': {
                            center: {lat: 62, lng: -110.0},
                            zoom: 3
                        },
                        'us': {
                            center: {lat: 37.1, lng: -95.7},
                            zoom: 3
                        },
                    },
                marker: null,
            }
        },

        mounted: function () {
            let self = this;
            const element = document.getElementById(this.mapName);
            // USA
            // let latitude = 40.730610;
            // let longitude = -98.935242;
            // CANADA
            let latitude = 55.369454;
            let longitude = -101.79182400;
            let zoom = 4;

            if (this.lat && this.lng) {
                latitude = this.lat;
                longitude = this.lng;
                zoom = 17;
            }
            if (this.value) {
                if (this.marker) {
                    this.marker.setMap(null);
                }
            }
            const options = {
                zoom: zoom,
                center: new google.maps.LatLng(latitude, longitude),
                streetViewControl: false,
            };
            this.map = new google.maps.Map(element, options);
            let address = this;
            this.marker = new google.maps.Marker({
                position: new google.maps.LatLng(latitude, longitude),
                map: this.map
            });
            google.maps.event.addListener(this.map, 'click', function (event) {
                console.log('map clicked');
                if (self.marker) {
                    self.marker.setMap(null);
                }
                self.marker = new google.maps.Marker({
                    position: event.latLng,
                    map: self.map
                });
                self.updateLocation(event.latLng.lat(), event.latLng.lng());
                let geocoder = new google.maps.Geocoder;
                geocoder.geocode({'location': event.latLng}, function (results, status) {
                    if (status === 'OK') {
                        console.log(results);
                        if (results[0]) {
                            address.value = results[0].formatted_address;
                        } else {
                            window.alert('No results found');
                        }
                    } else {
                        window.alert('Geocoder failed due to: ' + status);
                        error = false;
                    }
                });
            });

            this.setupAutocompleteField();


        },
        methods: {
            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.field.value || '';
                this.lat = this.field.lat || '';
                this.lng = this.field.lng || '';
            }
            ,

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(this.field.attribute, this.value || '');
                formData.append('lat', this.lat || '');
                formData.append('lng', this.lng || '');
            }
            ,

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            }
            ,

            updateLocation(lat, lng) {
                this.lat = lat.toFixed(6);
                this.lng = lng.toFixed(6);
            },
            handleKeypress(event) {
                console.log(event);
                if (event.key === 'Enter') {
                    // event.target.stop
                }
            },

            formatPlaceAddress(place) {
                if (!place.address_components) {
                    return 'Unknown place';
                }

                let streetNumber = this.getAddressComponent(place.address_components, 'street_number');
                let route = this.getAddressComponent(place.address_components, 'route');
                let locality = this.getAddressComponent(place.address_components, 'locality');
                let area = this.getAddressComponent(place.address_components, 'administrative_area_level_1');
                let postalCode = this.getAddressComponent(place.address_components, 'postal_code');
                let country = this.getAddressComponent(place.address_components, 'country');

                let part1 = [
                    (streetNumber && streetNumber.short_name || ''),
                    (route && route.short_name || '')
                ].join(' ');

                let part2 = (locality && locality.short_name || '');

                let part3 = [
                    (area && area.short_name || ''),
                    (postalCode && postalCode.short_name || '')
                ].join(' ');

                let part4 = (country && country.short_name || '');

                let components = [part1, part2, part3, part4];
                console.log(components);

                return [
                    (part1 || ''),
                    (part2 || ''),
                    (part3 || ''),
                    (part4 || ''),
                ].join(', ');

            },

            getAddressComponent(components, type) {
                let filteredComponents = components.filter(function (component) {
                    return component.types.includes(type);
                });

                if (filteredComponents.length > 0) {
                    return filteredComponents[0];
                }

                return null;
            },

            setupAutocompleteField() {
                let self = this;
                this.autocomplete = new google.maps.places.Autocomplete(this.$refs.aca);
                // Bind the map's bounds (viewport) property to the autocomplete object,
                // so that the autocomplete requests use the current map bounds for the
                // bounds option in the request.
                // this.autocomplete.bindTo('bounds', this.map);

                this.autocomplete.setComponentRestrictions(
                    {'country': ['us', 'ca']});

                // Set the data fields to return when the user selects a place.
                this.autocomplete.setFields(
                    ['address_components', 'geometry', 'icon', 'name']);

                this.autocomplete.setTypes(['address']);

                let addressField = this;
                this.autocomplete.addListener('place_changed', function () {
                    // infowindow.close();
                    self.marker.setVisible(false);
                    let place = self.autocomplete.getPlace();
                    if (!place.geometry) {
                        // User entered the name of a Place that was not suggested and
                        // pressed the Enter key, or the Place Details request failed.
                        window.alert("No details available for input: '" + place.name + "'");
                        return;
                    }

                    // If the place has a geometry, then present it on a map.
                    if (place.geometry.viewport) {
                        self.map.fitBounds(place.geometry.viewport);
                    } else {
                        self.map.setCenter(place.geometry.location);
                        self.map.setZoom(22);
                    }
                    self.marker.setPosition(place.geometry.location);
                    self.marker.setVisible(true);
                    self.updateLocation(place.geometry.location.lat(), place.geometry.location.lng());
                    let address = '';
                    console.log(place);
                    if (place.formatted_address) {
                        address = place.formatted_address;
                    } else {
                        // USA
                        // address = [
                        //     [
                        //         (place.address_components[0] && place.address_components[0].short_name || ''),
                        //         (place.address_components[1] && place.address_components[1].short_name || '')
                        //     ].join(' '),
                        //     (place.address_components[2] && place.address_components[2].short_name || ''),
                        //     [
                        //         (place.address_components[5] && place.address_components[5].short_name || ''),
                        //         (place.address_components[7] && place.address_components[7].short_name || '')
                        //     ].join(' '),
                        //     (place.address_components[6] && place.address_components[6].short_name || ''),
                        // ].join(', ');

                        // CANADA
                        // address = [
                        //     [
                        //         (place.address_components[0] && place.address_components[0].short_name || ''),
                        //         (place.address_components[1] && place.address_components[1].short_name || '')
                        //     ].join(' '),
                        //     (place.address_components[2] && place.address_components[2].short_name || ''),
                        //     [
                        //         (place.address_components[4] && place.address_components[4].short_name || ''),
                        //         (place.address_components[6] && place.address_components[6].short_name || '')
                        //     ].join(' '),
                        //     (place.address_components[7] && place.address_components[7].short_name || ''),
                        // ].join(', ');

                        address = self.formatPlaceAddress(place);
                    }
                    addressField.value = address;

                });
            }
        },
    }
</script>
