Nova.booting((Vue, router, store) => {
    Vue.component('index-map-address', require('./components/IndexField'))
    Vue.component('detail-map-address', require('./components/DetailField'))
    Vue.component('form-map-address', require('./components/FormField'))
})
