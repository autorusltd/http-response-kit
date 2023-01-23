<?php declare(strict_types=1);

namespace Arus\Http\Response;

/**
 * Import classes
 */
use Arus\Http\Response\Resource\Error;
use Arus\Http\Response\Resource\Errors;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Message\Response\HtmlResponse;
use Sunrise\Http\Message\Response\JsonResponse;
use Sunrise\Http\Message\ResponseFactory;
use Symfony\Component\Validator\ConstraintViolationInterface;
use InvalidArgumentException;

/**
 * Import functions
 */
use function is_array;

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
        /** @var ResponseFactory $factory */
        $factory = (new $this->responseFactory);
        return $factory->createResponse($status);
    }

    /**
     * @param mixed $content
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function html($content, int $status = 200) : ResponseInterface
    {
        return new HtmlResponse($status, $content);
    }

    /**
     * @param mixed $payload
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function json($payload, int $status = 200) : ResponseInterface
    {
        return new JsonResponse($status, $payload, $this->jsonOptions, $this->jsonDepth);
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
     * @param string|null $source
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
     * @param iterable $violations
     * @param int $status
     *
     * @return ResponseInterface
     *
     * @throws InvalidArgumentException
     */
    public function violations(iterable $violations, int $status = 400) : ResponseInterface
    {
        $errors = [];
        foreach ($violations as $violation) {
            if ($violation instanceof ConstraintViolationInterface) {
                $errors[] = new Error(
                    $violation->getMessage(),
                    $violation->getPropertyPath(),
                    $violation->getCode()
                );

                continue;
            }

            if (is_array($violation) && isset($violation['message'], $violation['source'])) {
                $errors[] = new Error(
                    $violation['message'],
                    $violation['source'],
                    $violation['code'] ?? null
                );

                continue;
            }

            if (is_array($violation) && isset($violation['message'], $violation['property'])) {
                $errors[] = new Error(
                    $violation['message'],
                    $violation['property'],
                    $violation['code'] ?? null
                );

                continue;
            }

            throw new InvalidArgumentException('Unexpected violation');
        }

        return $this->json(new Errors(...$errors), $status);
    }

    /**
     * @param array $violations
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function jsonViolations(array $violations, int $status = 400) : ResponseInterface
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = new Error(
                $violation['message'],
                $violation['property']
            );
        }

        return $this->json(new Errors(...$errors), $status);
    }
}
