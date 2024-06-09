<?php

namespace App\Abstract\Controller;

use App\Attribute\OnTelegramMessage;
use App\Attribute\OnTelegramQuery;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ReflectionClass;

abstract class TelegramBotController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface     $logger
    )
    {
        $this->token = $_ENV['TELEGRAM_BOT_TOKEN'];
    }

    public function webhook(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $data = $content['callback_query'] ?? $content['message'] ?? null;
        if (!$data) {
            return new Response('Invalid data', Response::HTTP_BAD_REQUEST);
        }

        $text = mb_strtolower($data['text'] ?? $data['data'] ?? 'unknown', 'UTF-8');
        $chatId = $data['chat']['id'] ?? null;

        if (!$chatId) {
            return new Response('Chat ID is missing', Response::HTTP_BAD_REQUEST);
        }

        $reflection = new ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $attrInstance = $attribute->newInstance();

                if ($this->checkAttribute($attrInstance, $text)) {
                    $this->{$method->getName()}($data, $chatId);
                    return $this->json(['status' => 'ok']);
                }
            }
        }

        $this->defaultAction($data, $chatId);
        return $this->json(['status' => 'ok']);
    }

    private function checkAttribute($attribute, string $text): bool
    {
        if ($attribute instanceof OnTelegramMessage) {
            if ($attribute->command && $attribute->command === $text) {
                return true;
            }
            if ($attribute->pattern && preg_match($attribute->pattern, $text)) {
                return true;
            }
        }
        if ($attribute instanceof OnTelegramQuery) {
            if ($attribute->command && $attribute->command === $text) {
                return true;
            }
            if ($attribute->pattern && preg_match($attribute->pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    protected function defaultAction($data, $chatId): void
    {
    }

    protected function SendMessage($chatId, $text, $keyboard = null): void
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        $this->ExecuteTelegram('sendMessage', $data);
    }

    protected function WithKeyboard(array $keyboard): array
    {
        return [
            'resize_keyboard' => true,
            'keyboard' => $keyboard
        ];
    }

    protected function WithInlineKeyboard(array $keyboard): array
    {
        return [
            'inline_keyboard' => $keyboard
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function ExecuteTelegram($command, $json_data): void
    {
        $url = "https://api.telegram.org/bot{$this->token}/$command";

        $response = $this->httpClient->request('POST', $url, [
            'json' => $json_data
        ]);

        if ($response->getStatusCode() !== 200) {
            $data = $response->toArray();
            if (isset($data['description'])) {
                // throw new \Exception("Error sending message: " . $data['description']);
                $this->logger->error("Error sending message: " . $data['description']);
            }
        }
    }
}
