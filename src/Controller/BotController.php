<?php

namespace App\Controller;

use App\Abstract\Controller\TelegramBotController;
use App\Attribute\OnTelegramMessage;
use App\Attribute\OnTelegramQuery;
use App\Services\BotService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BotController extends TelegramBotController
{
    private const MAIN_KEYBOARD = [
        [
            ['text' => 'Создать платеж', 'callback_data' => 'create-payment'],
            ['text' => 'Платежи', 'callback_data' => 'payments-list']
        ]
    ];

    public function __construct(
        HttpClientInterface         $httpClient,
        LoggerInterface             $logger,
        private readonly BotService $botService
    )
    {
        parent::__construct($httpClient, $logger);
    }

    #[Route(path: '/api/v1/telegram/webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        return parent::webhook($request);
    }

    #[OnTelegramMessage(command: '/start')]
    public function start($data, $chatId): void
    {
        if ($this->botService->start($data)) {
            $this->SendMessage($chatId, 'Добрый день!', $this->WithKeyboard(self::MAIN_KEYBOARD));
        } else {
            $this->SendMessage($chatId, 'Ошибка при обработке команды /start');
        }
    }

    #[OnTelegramQuery(command: 'create-payment')]
    public function createPayment($data, $chatId): void
    {
        $payment = $this->botService->createPayment($data);
        if ($payment) {
            $PaymentKeyboard = [
                [
                    ['text' => "Платеж {$payment->price} руб.", 'url' => $this->createPaymentUrl($payment)],
                    ['text' => 'Отменить', 'callback_data' => 'cancel-payment']
                ]
            ];
            $this->SendMessage($chatId, 'Платеж создан', $this->WithInlineKeyboard($PaymentKeyboard));
        } else {
            $this->SendMessage($chatId, 'Не удалось создать платеж');
        }
    }

    #[OnTelegramQuery(command: 'cancel-payment')]
    public function cancelPayment($data, $chatId): void
    {
        if ($this->botService->cancelPayment($data)) {
            $this->SendMessage($chatId, "Создан новый платеж со скидкой 10%.");
//            // Отправляем сообщение с задержкой
//            $this->laterMessage($chatId, "Ваш новый платеж со скидкой 10% готов.", 10);
        }
    }

    #[OnTelegramQuery(command: 'payments-list')]
    public function paymentsList($data, $chatId): void
    {
        $payments = $this->botService->getPayments($data);

        if (count($payments) > 0) {
            $PaymentsListKeyboard = array_map(function ($payment) {
                return [['text' => "Платеж {$payment->id}", 'callback_data' => "payment-{$payment->id}"]];
            }, $payments);

            $this->SendMessage($chatId, "У Вас " . count($payments) . " платежей:", $this->WithInlineKeyboard($PaymentsListKeyboard));
        } else {
            $this->SendMessage($chatId, 'У Вас нет платежей.');
        }
    }

    #[OnTelegramQuery(pattern: "/^payment-\d+$/")]
    public function PaymentDetails($data, $chatId): void
    {
        $paymentId = (int) str_replace('payment-', '', $data['data']);
        $payment = $this->botService->getPaymentDetails($paymentId);

        if ($payment) {
            $message = "Платёж {$payment->getId()}\n" .
                "Статус: {$payment->getStatus()}\n" .
                "Цена: {$payment->getPrice()} руб.\n" .
                "С учётом скидки: " . ($payment->isDiscount() ? 'Да' : 'Нет') . "\n" .
                "Дата создания: " . $payment->getCreatedAt()->format('Y-m-d H:i:s') . "\n";

            $paidAt = $payment->getPaidAt();
            if ($paidAt) {
                $message .= "Дата оплаты: " . $paidAt->format('Y-m-d H:i:s') . "\n";
            }

            $this->sendMessage($chatId, $message);
        } else {
            $this->sendMessage($chatId, "Платёж не найден.");
        }
    }

    private function laterMessage($chatId, $text, $delayInSeconds = 10)
    {
//        // Вы можете использовать асинхронный сервис или симулировать задержку здесь
//        $kernel = $this->container->get('kernel');
//        $command = sprintf('php %s/../bin/console app:delayed-message %d %s',
//            $kernel->getProjectDir(), $chatId, escapeshellarg($text));
//
//        $process = new \Symfony\Component\Process\Process($command);
//        $process->start();
//
//        return new Response('Message will be sent later', Response::HTTP_OK);
    }

    private function createPaymentUrl($payment): string
    {
        $site_url = $_ENV['SITE_URL'];
        return "$site_url/pay/{$payment->id}";
    }
}