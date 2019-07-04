<?php

namespace SoluzioneSoftware\Nova\Fields;

use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Translatable extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'translatable-field';

    private $locales = [];

    private $field;

    /**
     * Create a new field.
     *
     * @param Field $field
     */
    public function __construct($field)
    {
        parent::__construct($field->name, $field->attribute, $field->resolveCallback);

        $locales = app('translatable.locales')->all();
        $this->locales = array_combine($locales, array_map(function ($value){return strtoupper($value);}, $locales));
        $this->field = $field;

        $this->withMeta(['locales' => $this->locales, 'fields' => array_map(function () use ($field) {return $field;}, $this->locales)]);
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return mixed
     */
    protected function resolveAttribute($resource, $attribute)
    {
        $results = [];

        $translations = $resource->translations()
            ->get([config('translatable.locale_key'), $attribute])
            ->toArray();

        foreach ( $translations as $translation ) {
            $results[$translation[config('translatable.locale_key')]] = $translation[$attribute];
        }

        return $results;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param NovaRequest $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $requestData = $request->all();

        foreach ($this->locales as $localeCode => $locale) {
            if (is_null($values = Arr::get($requestData, "$localeCode")))
                continue;

            $values = json_decode($values, true);

            $model->setDefaultLocale($localeCode);
            $request->replace($values);

            $this->field->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
        }

        $request->replace($requestData);
    }
}
