<?php declare(strict_types=1);

namespace Arus\Http\Response\Tests;

/**
 * Import classes
 */
use Arus\Http\Response\ResponseFactoryAwareTrait;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Message\Response as SunriseResponse;
use Sunrise\Http\Message\ResponseFactory as SunriseResponseFactory;
use Laminas\Diactoros\Response as ZendResponse;
use Laminas\Diactoros\ResponseFactory as ZendResponseFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use InvalidArgumentException;

/**
 * ResponseFactoryTest
 */
class ResponseFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testDefaultPropertyValues() : void
    {
        $object = (new class () {
            use ResponseFactoryAwareTrait;

            public function getResponseFactory()
            {
                return $this->responseFactory;
            }

            public function getJsonOptions()
            {
                return $this->jsonOptions;
            }

            public function getJsonDepth()
            {
                return $this->jsonDepth;
            }
        });

        $this->assertSame(SunriseResponseFactory::class, $object->getResponseFactory());

        $this->assertSame(0, $object->getJsonOptions());

        $this->assertSame(512, $object->getJsonDepth());
    }

    /**
     * @return void
     */
    public function testCreateResponseThroughDefaultFactory() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->createResponse();

        $this->assertInstanceOf(SunriseResponse::class, $response);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateResponseThroughCustomFactory() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;

            public function __construct()
            {
                $this->responseFactory = ZendResponseFactory::class;
            }
        })->createResponse();

        $this->assertInstanceOf(ZendResponse::class, $response);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateResponseWithCustomStatusCode() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->createResponse(204);

        $this->assertSame(204, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateHtmlResponse() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->html('<br>');

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $this->assertSame('<br>', $response->getBody()->__toString());
    }

    /**
     * @return void
     */
    public function testCreateHtmlResponseWithCustomStatusCode() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->html('', 204);

        $this->assertSame(204, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateJsonResponse() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->json([null]);

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));

        $this->assertSame('[null]', $response->getBody()->__toString());
    }

    /**
     * @return void
     */
    public function testCreateJsonResponseWithCustomStatusCode() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->json(null, 204);

        $this->assertSame(204, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateJsonResponseWithCustomJsonOptions() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;

            public function __construct()
            {
                $this->jsonOptions = \JSON_NUMERIC_CHECK;
            }
        })->json(['123']);

        $this->assertSame('[123]', $response->getBody()->__toString());
    }

    /**
     * @return void
     */
    public function testCreateOkResponseWithoutOptionalParameters() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->ok();

        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            '{"meta":[],"data":[]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateOkResponseWithOptionalParameters() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->ok(['foo'], ['bar'], 400);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"meta":["bar"],"data":["foo"]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateErrorResponseWithoutOptionalParameters() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->error('foo');

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":null,"source":"","message":"foo"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateErrorResponseWithOptionalParameters() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->error('foo', 'bar', 'xxx', 500);

        $this->assertSame(500, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":"xxx","source":"bar","message":"foo"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateViolationsResponse() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations(new ConstraintViolationList([
            new ConstraintViolation('foo', null, [], null, 'bar', null, null, 'baz'),
            new ConstraintViolation('qux', null, [], null, 'quux', null, null, 'quuux')
        ]));

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":"baz","source":"bar","message":"foo"},' .
                '{"code":"quuux","source":"quux","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateViolationsResponseWithCustomStatusCode() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations(new ConstraintViolationList(), 500);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testCreateJsonViolationsResponse() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->jsonViolations([
            [
                'message' => 'foo',
                'property' => 'bar',
            ],
            [
                'message' => 'baz',
                'property' => 'qux',
            ],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":null,"source":"bar","message":"foo"},' .
                '{"code":null,"source":"qux","message":"baz"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testCreateJsonViolationsResponseWithCustomStatusCode() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->jsonViolations([], 500);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testViolationsWithObjects() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([
            new ConstraintViolation('foo', null, [], null, 'bar', null, null, 'baz'),
            new ConstraintViolation('qux', null, [], null, 'quux', null, null, 'quuux'),
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":"baz","source":"bar","message":"foo"},' .
                '{"code":"quuux","source":"quux","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testViolationsWithSourceableArrays() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([
            ['code' => 'foo', 'source' => 'bar', 'message' => 'baz'],
            ['code' => 'bar', 'source' => 'baz', 'message' => 'qux'],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":"foo","source":"bar","message":"baz"},' .
                '{"code":"bar","source":"baz","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testViolationsWithSourceableArraysWithoutCodes() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([
            ['source' => 'bar', 'message' => 'baz'],
            ['source' => 'baz', 'message' => 'qux'],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":null,"source":"bar","message":"baz"},' .
                '{"code":null,"source":"baz","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testViolationsWithPropertyableArrays() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([
            ['code' => 'foo', 'property' => 'bar', 'message' => 'baz'],
            ['code' => 'bar', 'property' => 'baz', 'message' => 'qux'],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":"foo","source":"bar","message":"baz"},' .
                '{"code":"bar","source":"baz","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testViolationsWithPropertyableArraysWithoutCodes() : void
    {
        $response = (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([
            ['property' => 'bar', 'message' => 'baz'],
            ['property' => 'baz', 'message' => 'qux'],
        ]);

        $this->assertSame(400, $response->getStatusCode());

        $this->assertSame(
            '{"errors":[{"code":null,"source":"bar","message":"baz"},' .
                '{"code":null,"source":"baz","message":"qux"}]}',
            $response->getBody()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testViolationsWithInvalidData() : void
    {
        $this->expectException(InvalidArgumentException::class);

        (new class () {
            use ResponseFactoryAwareTrait;
        })->violations([null]);
    }
}
