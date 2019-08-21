<template>
  <div>
    <div class="w-full py-6 px-8">
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

    <template v-for="(originalField, localeKey) in fields">
      <component
          v-show="localeKey === currentLocale"
          :is="'form-' + originalField.component"
          :errors="errors"
          :resource-id="originalField.resourceId"
          :resource-name="originalField.resourceName"
          :field="originalField"
          :ref="'field-' + originalField.attribute + '-' + localeKey"
      />
    </template>
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
                Object.keys(this.fields).forEach(locale => {
                    let f = this.$refs['field-' + this.fields[locale].attribute + '-' + locale][0];

                    f.setInitialValue();
                });
            },

            changeLocale(locale) {
                if(this.currentLocale !== locale){
                    this.currentLocale = locale;
                }
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                // todo: refactor this shit
                let aaa = {};

                Object.keys(this.locales).forEach(locale => {
                    let f = this.$refs['field-' + this.fields[locale].attribute + '-' + locale][0];

                    let data = new FormData;
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
        },
    }
</script>
