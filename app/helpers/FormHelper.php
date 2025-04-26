<?php

declare(strict_types=1);

namespace app\helpers;

class FormHelper
{
    protected array $values = [];

    protected array $errors = [];

    protected ?string $errorMessage = null;

    protected ?string $successMessage = null;

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function setSuccessMessage(?string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    public function hasErrorMessage(): bool
    {
        return $this->errorMessage !== null;
    }

    public function hasSuccessMessage(): bool
    {
        return $this->successMessage !== null;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setInputValue(string $name, $value): void
    {
        $this->values[$name] = (string) $value;
    }

    public function setInputValueIfEmpty(string $name, $value): void
    {
        if (isset($this->values[$name])) {
            return;
        }

        $this->values[$name] = (string) $value;
    }

    public function setInputError(string $name, string $error): void
    {
        $this->errors[$name] = $error;
    }

    public function getInputValue(string $name): string
    {
        return $this->values[$name] ?? '';
    }

    public function getInputError(string $name): string
    {
        return $this->errors[$name] ?? '';
    }

    public function getClassError(string $name, string $class): string
    {
        return isset($this->errors[$name]) ? $class : '';
    }

    public function setInputsValues(?array $inputsValues): void
    {
        if ($inputsValues === null) {
            return;
        }

        foreach ($inputsValues as $key => $value) {
            $this->setInputValue($key, $value);
        }
    }

    public function setInputsErrors(?array $inputsErrors): void
    {
        if ($inputsErrors === null) {
            return;
        }

        foreach ($inputsErrors as $key => $value) {
            $this->setInputError($key, $value);
        }
    }

    public function getSelectedValue(string $name, $value): string
    {
        return ($this->getInputValue($name) === $value) ? ' selected="selected"' : '';
    }
}
