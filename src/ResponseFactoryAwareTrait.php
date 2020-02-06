<?php declare(strict_types=1);

namespace Arus\Http\Response;

/**
 * Import classes
 */
use Sunrise\Http\Message\ResponseFactory;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * ResponseFactoryAwareTrait
 */
trait ResponseFactoryAwareTrait
{

    /**
     * @var int
     */
    protected $jsonOptions = 0;

    /**
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function empty(int $status) : ResponseInterface
    {
        return (new ResponseFactory)->createResponse($status);
    }

    /**
     * @param int $status
     * @param mixed $content
     *
     * @return ResponseInterface
     */
    public function html(int $status, $content) : ResponseInterface
    {
        return (new ResponseFactory)->createHtmlResponse($status, $content);
    }

    /**
     * @param int $status
     * @param mixed $payload
     *
     * @return ResponseInterface
     */
    public function json(int $status, $payload) : ResponseInterface
    {
        return (new ResponseFactory)->createJsonResponse($status, $payload, $this->jsonOptions);
    }

    /**
     * @param array $data
     * @param array $meta
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function ok(array $data = [], array $meta = [], int $status = 200) : ResponseInterface
    {
        return $this->json($status, [
            'meta' => $meta,
            'data' => $data,
        ]);
    }

    /**
     * @param string $message
     * @param string $source
     * @param mixed $code
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function error(string $message, string $source = null, $code = null, int $status = 400) : ResponseInterface
    {
        return $this->json($status, [
            'errors' => [[
                'code' => $code,
                'source' => $source,
                'message' => $message,
            ]],
        ]);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function violations(ConstraintViolationListInterface $violations, int $status = 400) : ResponseInterface
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'code' => $violation->getCode(),
                'source' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $this->json($status, [
            'errors' => $errors,
        ]);
    }
}
