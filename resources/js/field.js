Nova.booting((Vue, router, store) => {
    Vue.component('index-translatable-field', require('./components/IndexField'))
    Vue.component('detail-translatable-field', require('./components/DetailField'))
    Vue.component('form-translatable-field', require('./components/FormField'))
})
