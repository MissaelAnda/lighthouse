<?php

namespace Nuwave\Lighthouse\Execution;

use Nuwave\Lighthouse\Exceptions\GenericException;

class ErrorBuffer
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var string
     */
    protected $errorType;

    /**
     * @var \Closure
     */
    protected $exceptionResolver;

    /**
     * ErrorBuffer constructor.
     *
     * @param string        $errorType
     * @param \Closure|null $exceptionResolver
     */
    public function __construct(string $errorType = 'generic', \Closure $exceptionResolver = null)
    {
        $this->errorType = $errorType;
        $this->exceptionResolver = $exceptionResolver ?? $this->defaultExceptionResolver();
    }

    /**
     * Construct a default exception resolver.
     *
     * @return \Closure
     */
    protected function defaultExceptionResolver(): \Closure
    {
        return function (string $errorMessage) {
            return (new GenericException($errorMessage))
                ->setExtensions([$this->errorType => $this->errors])
                ->setCategory($this->errorType);
        };
    }

    /**
     * Set the Exception resolver.
     *
     * @param \Closure $exceptionResolver
     *
     * @return static
     */
    public function setExceptionResolver(\Closure $exceptionResolver): self
    {
        $this->exceptionResolver = $exceptionResolver;

        return $this;
    }

    /**
     * Resolve the exception by calling the exception handler with the given args.
     *
     * @param mixed ...$args
     *
     * @return mixed
     */
    protected function resolveException(...$args)
    {
        return ($this->exceptionResolver)(...$args);
    }

    /**
     * Push an error message into the buffer.
     *
     * @param $key
     * @param $errorMessage
     *
     * @return static
     */
    public function push(string $errorMessage, string $key = null): self
    {
        if (null === $key) {
            $this->errors[] = $errorMessage;

            return $this;
        }

        $this->errors[$key][] = $errorMessage;

        return $this;
    }

    /**
     * Flush the errors.
     *
     * @param string $errorMessage
     *
     * @throws \Exception
     */
    public function flush(string $errorMessage)
    {
        if (! $this->hasErrors()) {
            return;
        }

        $exception = $this->resolveException($errorMessage, $this);

        $this->clearErrors();

        throw $exception;
    }

    /**
     * Reset the errors to an empty array.
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * Get the error type.
     *
     * @return string
     */
    public function errorType(): string
    {
        return $this->errorType;
    }

    /**
     * Set the error type.
     *
     * @param string $errorType
     *
     * @return static
     */
    public function setErrorType(string $errorType): self
    {
        $this->errorType = $errorType;

        return $this;
    }

    /**
     * Do we have any errors yet?
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return \count($this->errors) > 0;
    }
}