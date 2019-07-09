<template>
    <div>
        <div class="w-full py-6">
            <a
                class="inline-block font-bold cursor-pointer mr-2 animate-text-color select-none"
                :class="{ 'text-60': localeKey !== currentLocale, 'text-primary border-b-2': localeKey === currentLocale }"
                :key="`a-${localeKey}`"
                v-for="(locale, localeKey) in field.locales"
                @click="changeLocale(localeKey)"
            >
                {{ locale }}
            </a>
        </div>

        <template v-for="(originalField, localeKey) in field.fields">
            <component
                v-show="localeKey === currentLocale"
                :is="'detail-' + originalField.component"
                :field="originalField"
                :resource-id="originalField.resourceId"
                :resource-name="originalField.resourceName"
            />
        </template>
    </div>
</template>

<script>
    export default {
        props: ['resource', 'resourceName', 'resourceId', 'field'],

        data() {
            return {
                currentLocale: null,
                locales: this.field.locales,
                fields: this.field.fields,
            }
        },

        /**
         * Mount the component.
         */
        mounted() {
            console.log(this.field.fields);

            this.currentLocale = Object.keys(this.locales)[0] || null;
        },

        methods: {
            changeLocale(locale) {
                if(this.currentLocale !== locale){
                    this.currentLocale = locale;
                }
            },
        },
    }
</script>
