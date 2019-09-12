<template>
    <div>
        <div class="w-full py-6 px-8">
            <a
                class="inline-block font-bold cursor-pointer mr-2 animate-text-color select-none"
                :class="{ 'text-60': localeKey !== currentLocale, 'text-primary border-b-2': localeKey === currentLocale }"
                :key="`a-${localeKey}`"
                v-for="(locale, localeKey) in locales"
                @click="changeLocale(localeKey)"
            >
                {{ locale }}
            </a>
        </div>

        <component :is="'form-' + originalField.component" :resource-id="resourceId"
                   :resource-name="resourceName"
                   :field="currentLocaleField"
                   :ref="'field-' + originalField.attribute"
                   :key="currentLocale + '-' + originalField.attribute"
        />
    </div>
</template>



<script>
    import { FormField, HandlesValidationErrors } from 'laravel-nova'

    export default {
        mixins: [FormField, HandlesValidationErrors],

        props: ['resourceName', 'resourceId', 'field'],

        data() {
            return {
                locales: this.field.locales,
                currentLocale: null,
                fields: this.field.fields,
                originalField: this.field.originalField,
            }
        },

        mounted() {
            this.currentLocale = Object.keys(this.locales)[0] || null;
        },

        methods: {
            /**
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.$refs[`field-${this.originalField.attribute}`].setInitialValue();
            },

            changeLocale(locale) {
                this.saveCurrentValue();

                this.currentLocale = locale;
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                // todo: refactor this shit
                let f;
                let aaa = {};

                Object.keys(this.fields).forEach(locale => {
                    let data = new FormData;

                    this.changeLocale(locale);
                    f = this.$refs['field-' + this.originalField.attribute];
                    f.handleChange(this.fields[locale].value);

                    f.fill(data);

                    for (const [key, value]  of data.entries()) {
                        if (aaa[locale] === undefined){
                            aaa[locale] = {};
                        }
                        aaa[locale][key] = value;
                    }
                });

                Object.keys(aaa).forEach(locale => {
                    let previousFormData = JSON.parse(formData.get(locale) || JSON.stringify({}));
                    let newFormData = aaa[locale];

                    Object.keys(newFormData).forEach(key => { previousFormData[key] = newFormData[key]; });

                    formData.set(locale, JSON.stringify(previousFormData));
                });
            },

            saveCurrentValue() {
                this.fields[this.currentLocale].value = this.$refs['field-' + this.originalField.attribute].value;
            },
        },

        computed: {
            currentLocaleField(){
                return this.fields[this.currentLocale] || this.originalField;
            }
        }
    }
</script>
