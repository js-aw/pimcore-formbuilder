<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ToggleElementAction implements ActionInterface
{
    use ActionTrait;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $state = NULL;

    /**
     * @param               $validationState
     * @param               $formData
     * @param               $ruleId
     * @return ReturnStackInterface
     */
    public function apply($validationState, $formData, $ruleId)
    {
        $data = [];
        $state = $this->getState();
        foreach ($this->getFields() as $conditionFieldName) {
            $toggleState = $validationState === TRUE ? 'hide' : 'show';
            $data[$conditionFieldName] = $state === $toggleState ? 'fb-cl-hide-element' : '';
        }

        return new FieldReturnStack('toggleElement', $data);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}