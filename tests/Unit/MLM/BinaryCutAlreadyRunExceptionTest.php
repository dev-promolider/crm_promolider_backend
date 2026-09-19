<?php

namespace Tests\Unit\MLM;

use App\Exceptions\MLM\BinaryCutAlreadyRunException;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use Error;
use Exception;
use PHPUnit\Framework\TestCase;

class BinaryCutAlreadyRunExceptionTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_with_only_periodo()
    {
        $exception = new BinaryCutAlreadyRunException('2026-09');

        $this->assertSame('2026-09', $exception->periodo);
        $this->assertNull($exception->ejecutadoEl);
        $this->assertSame(409, $exception->getCode());
        $this->assertStringContainsString('El corte binario del periodo 2026-09 ya se ejecuto.', $exception->getMessage());
        $this->assertStringNotContainsString('(se ejecuto el', $exception->getMessage());
    }

    /** @test */
    public function it_formats_message_correctly_with_string_executed_at()
    {
        $fecha = '2026-09-15 14:30:00';
        $exception = new BinaryCutAlreadyRunException('2026-09-W02', $fecha);

        $this->assertSame('2026-09-W02', $exception->periodo);
        $this->assertSame($fecha, $exception->ejecutadoEl);
        $this->assertSame(409, $exception->getCode());
        $this->assertStringContainsString("El corte binario del periodo 2026-09-W02 ya se ejecuto (se ejecuto el {$fecha}).", $exception->getMessage());
    }

    /** @test */
    public function it_supports_datetime_and_datetime_immutable_instances()
    {
        $dt = new DateTime('2026-09-18 20:00:00');
        $exception1 = new BinaryCutAlreadyRunException('2026-09', $dt);

        $this->assertSame('2026-09-18 20:00:00', $exception1->ejecutadoEl);
        $this->assertStringContainsString('(se ejecuto el 2026-09-18 20:00:00)', $exception1->getMessage());

        $dtImmutable = new DateTimeImmutable('2026-09-19 09:15:00');
        $exception2 = new BinaryCutAlreadyRunException('2026-09', $dtImmutable);

        $this->assertSame('2026-09-19 09:15:00', $exception2->ejecutadoEl);
        $this->assertStringContainsString('(se ejecuto el 2026-09-19 09:15:00)', $exception2->getMessage());
    }

    /** @test */
    public function it_supports_carbon_instances()
    {
        $carbon = Carbon::create(2026, 9, 20, 11, 45, 30);
        $exception = new BinaryCutAlreadyRunException('2026-09', $carbon);

        $this->assertSame('2026-09-20 11:45:30', $exception->ejecutadoEl);
        $this->assertStringContainsString('(se ejecuto el 2026-09-20 11:45:30)', $exception->getMessage());
    }

    /** @test */
    public function it_supports_custom_http_code_and_previous_exception()
    {
        $previous = new Exception('Causa raíz');
        $exception = new BinaryCutAlreadyRunException('2026-09', null, 422, $previous);

        $this->assertSame(422, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /** @test */
    public function it_stores_periodo_and_ejecutado_el_as_public_typed_properties()
    {
        $reflector = new \ReflectionClass(BinaryCutAlreadyRunException::class);

        $periodoProp = $reflector->getProperty('periodo');
        $this->assertTrue($periodoProp->isPublic());
        $this->assertSame('string', (string) $periodoProp->getType());

        $ejecutadoElProp = $reflector->getProperty('ejecutadoEl');
        $this->assertTrue($ejecutadoElProp->isPublic());
        $this->assertTrue($ejecutadoElProp->getType()->allowsNull());
    }

    /** @test */
    public function it_is_backward_compatible_with_existing_binary_cut_service_usage()
    {
        // En BinaryCutService::reservarPeriodo:
        // throw new BinaryCutAlreadyRunException($periodo, $existente->executed_at);
        $periodo = '2026-09';
        $executedAt = '2026-09-10 12:00:00';

        $exception = new BinaryCutAlreadyRunException($periodo, $executedAt);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame($periodo, $exception->periodo);
        $this->assertSame($executedAt, $exception->ejecutadoEl);
        $this->assertIsString($exception->getMessage());
    }
}
