<?php declare(strict_types=1);

namespace Arus\Http\Response\Resource;

/**
 * Errors
 */
class Errors implements ResourceInterface
{

    /**
     * The collection errors
     *
     * @var Error[]
     */
    private $errors;

    /**
     * Constructor of the class
     *
     * @param Error ...$errors
     */
    public function __construct(Error ...$errors)
    {
        $this->errors = $errors;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'errors' => $this->errors,
        ];
    }
}
