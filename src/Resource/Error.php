<?php declare(strict_types=1);

namespace Arus\Http\Response\Resource;

/**
 * Error
 */
class Error implements ResourceInterface
{

    /**
     * The error message
     *
     * @var string
     */
    private $message;

    /**
     * The error source
     *
     * @var string
     */
    private $source;

    /**
     * The error code
     *
     * @var mixed
     */
    private $code;

    /**
     * Construct of the class
     *
     * @param string $message
     * @param string $source
     * @param mixed $code
     */
    public function __construct(string $message, string $source = null, $code = null)
    {
        $this->message = $message;
        $this->source = $source;
        $this->code = $code;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'code' => $this->code,
            'source' => $this->source,
            'message' => $this->message,
        ];
    }
}
