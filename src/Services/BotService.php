<?php

namespace App\Services;

class BotService
{
    public function start($data): bool
    {
        return true;
    }

    public function createPayment($data): bool
    {
        return true;
    }

    public function cancelPayment($data): bool
    {
        return true;
    }

    public function getPayments($data): array
    {
        return [];
    }

    public function getPaymentDetails($id): array
    {
        return [];
    }
}