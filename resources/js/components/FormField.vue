<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div class="google-map" :id="mapName"></div><br>
            <input
                    :id="field.name"
                    type="text"
                    class="w-full form-control form-input form-input-bordered"
                    :class="errorClasses"
                    :placeholder="field.name"
                    v-model="value"
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
        border:solid 1px #ccc;
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
          }
        },

        mounted: function () {
            let self = this;
            let map;
            const element = document.getElementById(this.mapName);
            let latitude = 40.730610;
            let longitude = -98.935242;
            let zoom = 4;

            if (this.lat && this.lng) {
                latitude = this.lat;
                longitude = this.lng;
                zoom = 17;
            }
            if (this.value){
                if (previousMarker) {
                    previousMarker.setMap(null);
                }
            }
            const options = {
                zoom: zoom,
                center: new google.maps.LatLng(latitude, longitude),
                streetViewControl: false,
            };
            map = new google.maps.Map(element, options);
            let previousMarker;
            let address = this;
            previousMarker = new google.maps.Marker({
                position: new google.maps.LatLng(latitude, longitude),
                map: map
            });
            google.maps.event.addListener(map, 'click', function(event) {
                if (previousMarker) {
                    previousMarker.setMap(null);
                }
                previousMarker = new google.maps.Marker({
                    position: event.latLng,
                    map: map
                });
                self.updateLocation(event.latLng.lat(), event.latLng.lng());
                let geocoder = new google.maps.Geocoder;
                geocoder.geocode({'location': event.latLng}, function(results, status) {
                    if (status === 'OK') {
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
        },
        methods: {
            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.field.value || '';
                this.lat = this.field.lat || '';
                this.lng = this.field.lng || '';
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(this.field.attribute, this.value || '');
                formData.append('lat', this.lat || '');
                formData.append('lng', this.lng || '');
            },

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            },

            updateLocation(lat, lng) {
                this.lat = lat.toFixed(6);
                this.lng = lng.toFixed(6);
            }
        },
    }
</script>
