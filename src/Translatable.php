<?php

namespace SoluzioneSoftware\Nova\Fields;

use Exception;
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
        $this->fields = array_map(function () use ($field) {return clone $field;}, $this->locales);

        $this->withMeta([
            'locales' => $this->locales,
            'fields' => $this->fields
        ]);

        $this->indexLocale(app('translatable.locales')->current());

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
     * {@inheritdoc}
     */
    public function resolve($resource, $attribute = null)
    {
        foreach ($this->fields as $localeCode => $field) {
            $resource->setDefaultLocale($localeCode);
            $field->resolve($resource, $attribute);
        }
        return;
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
