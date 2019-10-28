<template>
    <component
        :is="'index-' + originalField.component"
        :field="localizedField"
        :resource-name="resourceName"
    />
</template>

<script>
    export default {
        props: ['resourceName', 'field'],

        data() {
            return {
                originalField: this.field.originalField,
                indexLocale: this.field.indexLocale,
            }
        },

        computed: {
            localizedField(){
                return this.field.fields[this.indexLocale];
            }
        }
    }
</script>
