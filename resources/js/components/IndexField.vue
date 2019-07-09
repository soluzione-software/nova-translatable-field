<template>
    <component
        :is="'index-' + originalField.component"
        :field="originalField"
        :resource-name="originalField.resourceName"
    />
</template>

<script>
    export default {
        props: ['resourceName', 'field'],

        data() {
            return {
                originalField: this.field.fields[this.field.indexLocale],
            }
        },
    }
</script>
