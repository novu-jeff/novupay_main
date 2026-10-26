<?php

namespace Tests\Unit;

use App\Services\OverdueQrService;
use PHPUnit\Framework\TestCase;

class OverdueQrServiceTest extends TestCase
{
    public function test_amount_due_uses_total_not_stored_after_due_amount(): void
    {
        $computed = OverdueQrService::computeStaritaAmounts(160, 0, 176, 176, 16);

        $this->assertSame(160.0, $computed['amount_due']);
        $this->assertSame(176.0, $computed['amount_after_due']);
    }

    public function test_checkout_before_due_never_includes_penalty(): void
    {
        $this->assertSame(160.0, OverdueQrService::checkoutAmount(160, 176, false));
    }

    public function test_checkout_after_due_uses_amount_after_due(): void
    {
        $this->assertSame(176.0, OverdueQrService::checkoutAmount(160, 176, true));
    }

    public function test_unwraps_penalty_when_total_is_missing(): void
    {
        $computed = OverdueQrService::computeStaritaAmounts(0, 0, 176, 176, 16);

        $this->assertSame(160.0, $computed['amount_due']);
        $this->assertSame(176.0, $computed['amount_after_due']);
    }

    public function test_doubled_amount_after_due_is_collapsed_to_one_penalty(): void
    {
        $computed = OverdueQrService::computeStaritaAmounts(160, 0, 176, 192, 16);

        $this->assertSame(160.0, $computed['amount_due']);
        $this->assertSame(176.0, $computed['amount_after_due']);
    }
}
