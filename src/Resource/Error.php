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
     * @var null|string
     */
    private $code;

    /**
     * Construct of the class
     *
     * @param string $message
     * @param string $source
     * @param mixed $code
     */
    public function __construct(string $message, string $source = '', $code = null)
    {
        if (!is_null($code)) {
            $code = (string) $code;
        }

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
