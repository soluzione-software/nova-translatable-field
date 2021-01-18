<?php

namespace SoluzioneSoftware\Nova\Fields;

use Exception;
use Illuminate\Database\Eloquent\Model;
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

    /** @var Field */
    private $field;

    /** @var array */
    private $fields;

    /**
     * Create a new field.
     *
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        parent::__construct($field->name, $field->attribute, $field->resolveCallback);

        $locales = app('translatable.locales')->all();
        $this->locales = array_combine($locales, array_map(function ($value){return strtoupper($value);}, $locales));
        $this->field = $field;

        $fields = array_map(function ($locale) use ($field) {return $this->localizeField(clone $field, $locale);}, $locales);
        $this->fields = array_combine($locales, $fields);

        $this->withMeta([
            'locales' => $this->locales,
            'fields' => $this->fields,
            'originalField' => $this->field,
        ]);

        $this->indexLocale(app()->getLocale());

        $this->showOnIndex = $this->field->showOnIndex;
        $this->showOnDetail = $this->field->showOnDetail;
        $this->showOnCreation = $this->field->showOnCreation;
        $this->showOnUpdate = $this->field->showOnUpdate;
    }

    public function indexLocale($locale)
    {
        return $this->withMeta(['indexLocale' => $locale]);
    }

    /**
     * @param \Astrotomic\Translatable\Contracts\Translatable $resource
     * @param string|null $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        /** @var Field $field */
        foreach ($this->fields as $localeCode => $field) {
            $resource->setDefaultLocale($localeCode);
            $field->resolve($resource, $this->field->attribute);
        }

        return;
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
        return $resource->translations->pluck($attribute, config('translatable.locale_key'));
    }

    protected function localizeField(Field $field, string $locale)
    {
        $field->attribute = $this->localizeAttribute($locale, $field->attribute);
        return $field;
    }

    protected function localizeAttribute(string $locale, string $attribute = null)
    {
        return is_null($attribute) ? null : "translatable_{$locale}_{$attribute}";
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param NovaRequest $request
     * @param  string  $requestAttribute
     * @param  Model  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        /** @var array $requestData */
        $requestData = $request->all();

        foreach ($this->locales as $localeCode => $locale) {
            $value = $request->get($this->localizeAttribute($localeCode, $requestAttribute));

            $requestData[$requestAttribute] = $value;
            $request->replace($requestData);

            $model->setDefaultLocale($localeCode);

            $this->field->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
        }
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component(),
            'prefixComponent' => true,
            'indexName' => $this->name,
            'name' => $this->name,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'panel' => $this->panel,
            'sortable' => $this->sortable,
            'nullable' => $this->nullable,
            'readonly' => $this->isReadonly(app(NovaRequest::class)),
            'textAlign' => $this->textAlign,
        ], $this->meta());
    }

    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    function __get($name){
        if (isset($this->$name)){
            return $this->$name;
        }
        elseif (isset($this->field->$name)){
            return $this->field->$name;
        }

        throw new Exception("Undefined property \"$name\"");
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->field, $method))
            return call_user_func_array($this->field->$method, $arguments);

        return parent::__call($method, $arguments);
    }
}
