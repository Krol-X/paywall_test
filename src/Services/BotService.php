<?php

namespace App\Services;

use App\Entity\Payment;

class BotService
{
    public function start($data): bool
    {
        return true;
    }

    public function createPayment($data): ?Payment
    {
        return new Payment($data);
    }

    public function cancelPayment($data): bool
    {
        return true;
    }

    public function getPayments($data): array
    {
        return [];
    }

    public function getPaymentDetails($id): ?Payment
    {
        return null;
    }
}