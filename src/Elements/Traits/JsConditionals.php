<?php

namespace Kompo\Elements\Traits;

trait JsConditionals
{
    protected static $jsConditionOperators = [
        '==' => '==',
        '===' => '===',
        '!=' => '!=',
        '!==' => '!==',
        '>' => '>',
        '<' => '<',
        '>=' => '>=',
        '<=' => '<=',
        'contains' => '.includes',
    ];

    /**
     * Show element when field matches condition.
     * Usage: ->jsShowWhen('field', 'value')           // assumes ==
     *        ->jsShowWhen('field', '!=', 'value')     // explicit operator
     */
    public function jsShowWhen($fieldName, ...$args)
    {
        return $this->jsConditionalConfig('show', $fieldName, $this->buildJsCondition($args));
    }

    public function jsHideWhen($fieldName, ...$args)
    {
        return $this->jsConditionalConfig('hide', $fieldName, $this->buildJsCondition($args));
    }

    public function jsEnableWhen($fieldName, ...$args)
    {
        return $this->jsConditionalConfig('enable', $fieldName, $this->buildJsCondition($args));
    }

    public function jsDisableWhen($fieldName, ...$args)
    {
        return $this->jsConditionalConfig('disable', $fieldName, $this->buildJsCondition($args));
    }

    // --- Filled / Empty ---

    public function jsShowWhenFilled($fieldName)
    {
        return $this->jsConditionalConfig('show', $fieldName, "val !== null && val !== '' && val !== undefined");
    }

    public function jsHideWhenFilled($fieldName)
    {
        return $this->jsConditionalConfig('hide', $fieldName, "val !== null && val !== '' && val !== undefined");
    }

    public function jsShowWhenEmpty($fieldName)
    {
        return $this->jsConditionalConfig('show', $fieldName, "val === null || val === '' || val === undefined");
    }

    public function jsEnableWhenFilled($fieldName)
    {
        return $this->jsConditionalConfig('enable', $fieldName, "val !== null && val !== '' && val !== undefined");
    }

    // --- In (array membership) ---

    public function jsShowWhenIn($fieldName, array $values)
    {
        return $this->jsConditionalConfig('show', $fieldName, $this->buildJsInCondition($values));
    }

    public function jsHideWhenIn($fieldName, array $values)
    {
        return $this->jsConditionalConfig('hide', $fieldName, $this->buildJsInCondition($values));
    }

    // --- Any (multiple OR conditions) ---

    public function jsShowWhenAny($fieldName, array $conditions)
    {
        return $this->jsConditionalConfig('show', $fieldName, $this->buildJsAnyCondition($conditions));
    }

    public function jsHideWhenAny($fieldName, array $conditions)
    {
        return $this->jsConditionalConfig('hide', $fieldName, $this->buildJsAnyCondition($conditions));
    }

    // --- Callback (custom JS function) ---

    public function jsShowWhenCallback($fieldName, $jsCallback)
    {
        return $this->jsConditionalConfig('show', $fieldName, "({$jsCallback})(val)");
    }

    public function jsHideWhenCallback($fieldName, $jsCallback)
    {
        return $this->jsConditionalConfig('hide', $fieldName, "({$jsCallback})(val)");
    }

    // --- Class conditional (separate config key) ---

    public function jsClassWhen($fieldName, ...$args)
    {
        $classFalse = array_pop($args);
        $classTrue = array_pop($args);

        $condition = $this->buildJsCondition($args);

        return $this->config([
            'jsClassConditional' => [
                'field' => $fieldName,
                'condition' => $condition,
                'classTrue' => $classTrue,
                'classFalse' => $classFalse,
            ],
        ]);
    }

    // ===== Internal builders =====

    protected function jsConditionalConfig($type, $fieldName, $condition)
    {
        // Start hidden for 'show' type to prevent flash before JS evaluates
        if (in_array($type, ['show', 'enable'])) {
            $this->class('vlHide')->style('display:none');
        }

        return $this->config([
            'jsConditional' => [
                'type' => $type,
                'field' => $fieldName,
                'condition' => $condition,
            ],
        ]);
    }

    protected function buildJsCondition(array $args)
    {
        if (count($args) === 1) {
            $operator = '==';
            $value = $args[0];
        } else {
            $operator = $args[0];
            $value = $args[1];
        }

        $jsOp = static::$jsConditionOperators[$operator] ?? '==';
        $valueJson = json_encode($value);

        if ($operator === 'contains') {
            return "String(val){$jsOp}({$valueJson})";
        }

        if (is_bool($value)) {
            $boolLiteral = $value ? 'true' : 'false';

            return "!!val {$jsOp} {$boolLiteral}";
        }

        return "String(val) {$jsOp} String({$valueJson})";
    }

    protected function buildJsInCondition(array $values)
    {
        $valuesJson = json_encode(array_map('strval', array_values($values)));

        return "{$valuesJson}.includes(String(val))";
    }

    protected function buildJsAnyCondition(array $conditions)
    {
        $parts = [];

        foreach ($conditions as $cond) {
            if (!is_array($cond)) {
                $cond = [$cond];
            }
            $parts[] = '(' . $this->buildJsCondition($cond) . ')';
        }

        return implode(' || ', $parts);
    }
}
