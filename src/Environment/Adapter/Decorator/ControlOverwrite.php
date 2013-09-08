<?php

namespace Environment\Adapter\Decorator;

use Environment\Adapter\Behavior;
use Environment\Exception;

class ControlOverwrite implements Behavior\Write,
                                  Behavior\Read
{
    const PREVENT = false;
    const ALLOW = true;
    private $decoratedAdapter;
    private $optionAllowOverwrite;

    public function __construct(Behavior\Adapter $adapter, $allowOverwrite=self::PREVENT)
    {
        $this->decoratedAdapter = $adapter;
        $this->optionAllowOverwrite = (boolean) $allowOverwrite;
    }

    private function hasValue($name)
    {
        try {
            $this->get($name);
            return true;
        } catch (Exception\KeyNotFound $e) {
            return false;
        }
    }

    public function get($name)
    {
        if (!$this->decoratedAdapter instanceof Behavior\Read) {
            $baseExceptionMessage = 'Class \'%s\' does not have READ behavior.';
            $exceptionMessage = sprintf($baseExceptionMessage, get_class($this->decoratedAdapter));
            throw new \InvalidArgumentException($exceptionMessage);
        }

        $value = $this->decoratedAdapter->get($name);
        if (empty($value)) {
            throw new Exception\KeyNotFound($name);
        }

        return $value;
    }

    public function set($name, $value)
    {
        if (!$this->decoratedAdapter instanceof Behavior\Write) {
            $baseExceptionMessage = 'Class \'%s\' does not have WRITE behavior.';
            $exceptionMessage = sprintf($baseExceptionMessage, get_class($this->decoratedAdapter));
            throw new \InvalidArgumentException($exceptionMessage);
        }

        if ($this->hasValue($name) && false === $this->optionAllowOverwrite) {
            $exceptionMessage = sprintf('\'%s\' is already set, and overwrite is not allowed.', $name);
            throw new Exception\WriteNotAllowed($exceptionMessage);
        }

        $this->decoratedAdapter->set($name, $value);
    }
}