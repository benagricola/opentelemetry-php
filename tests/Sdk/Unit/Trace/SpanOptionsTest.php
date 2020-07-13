<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\SpanOptions;
use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class SpanOptionsTest extends TestCase
{
    public function testShouldCreateSpanFromOptions()
    {
        $tracer = $this->getTracer();
        $spanOptions = new SpanOptions($tracer, 'web');

        $global = $tracer->getActiveSpan();
        $spanOptions->setParentSpan($global);

        // Create span from options
        $web = $spanOptions->toSpan();

        // Make sure created span is not activated
        $this->assertSame($tracer->getActiveSpan(), $global);

        $this->assertSame($global->getContext()->getTraceId(), $web->getContext()->getTraceId());
        $this->assertEquals($web->getParent(), $global->getContext());
        $this->assertNotNull($web->getStartTimestamp());
        $this->assertTrue($web->isRecording());
        $this->assertNull($web->getDuration());
    }

    public function testShouldCreateAndSetActiveSpanFromOptions()
    {
        $tracer = $this->getTracer();
        $spanOptions = new SpanOptions($tracer, 'web');
        $global = $tracer->getActiveSpan();
        $this->assertSame($spanOptions, $spanOptions->setSpanName('web2'));
        $this->assertSame($spanOptions, $spanOptions->addStartTimestamp(1234));

        $web = $spanOptions->toActiveSpan();

        // Make sure created span is not activated
        $this->assertSame($tracer->getActiveSpan(), $web);

        // Assert previously set vars
        $this->assertEquals('web2', $web->getSpanName());
        $this->assertEquals(1234, $web->getStartTimestamp());
    }

    public function testShouldCreateCorrectSpanAttributes()
    {
        $tracer = $this->getTracer();
        $spanOptions = new SpanOptions($tracer, 'web');
        $global = $tracer->getActiveSpan();

        $attribRaw = [
            'attr_1' => 'value_1',
            'attr_2' => 2,
            'attr_3' => true,
            'attr_4' => 3.14159,
            'attr_5' => [1,2,3,4,5],
            'attr_6' => [1.1,2.2,3.3,4.4,5.5],
        ];

        $attributes = new Attributes($attribRaw);

        $spanOptions->addAttributes($attributes);

        $web = $spanOptions->toActiveSpan();

        // Check that span attributes are the ones passed in to spanOptions
        $this->assertSame($attributes, $web->getAttributes());
    }

    protected function getTracer(): Tracer
    {
        $tracerProvider = new TracerProvider();

        return $tracerProvider->getTracer('OpenTelemetry.TracerTest');
    }
}
