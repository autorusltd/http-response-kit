<?php declare(strict_types=1);

namespace Arus\Http\Response;

/**
 * Import classes
 */
use Arus\Http\Response\Resource\Error;
use Arus\Http\Response\Resource\Errors;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Message\ResponseFactory;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * ResponseFactoryAwareTrait
 */
trait ResponseFactoryAwareTrait
{

    /**
     * @var string
     */
    protected $responseFactory = ResponseFactory::class;

    /**
     * @var int
     */
    protected $jsonOptions = 0;

    /**
     * @var int
     */
    protected $jsonDepth = 512;

    /**
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function createResponse(int $status = 200) : ResponseInterface
    {
        return (new $this->responseFactory)
            ->createResponse($status);
    }

    /**
     * @param mixed $content
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function html($content, int $status = 200) : ResponseInterface
    {
        return (new $this->responseFactory)
            ->createHtmlResponse($status, $content);
    }

    /**
     * @param mixed $payload
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function json($payload, int $status = 200) : ResponseInterface
    {
        return (new $this->responseFactory)
            ->createJsonResponse($status, $payload, $this->jsonOptions, $this->jsonDepth);
    }

    /**
     * @param mixed $data
     * @param array $meta
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function ok($data = [], array $meta = [], int $status = 200) : ResponseInterface
    {
        return $this->json([
            'meta' => $meta,
            'data' => $data,
        ], $status);
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
        $error = new Error($message, $source, $code);

        return $this->json(new Errors($error), $status);
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
            $errors[] = new Error(
                $violation->getMessage(),
                $violation->getPropertyPath(),
                $violation->getCode()
            );
        }

        return $this->json(new Errors(...$errors), $status);
    }
}
