<template>
    <panel-item :field="field">
        <template slot="value">
            <excerpt :content="content" :plain-text="true" :should-show="field.shouldShow" />
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    data() {
        return {
            currentLocale: null,
        }
    },

    /**
     * Mount the component.
     */
    mounted() {
        this.currentLocale = Object.keys(this.field.locales)[0] || null;
        console.log(this.field.value);
    },

    computed: {
        content() {
            return this.field.value[this.currentLocale]
        },
    },
}
</script>
