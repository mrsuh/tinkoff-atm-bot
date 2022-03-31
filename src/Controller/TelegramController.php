<?php

namespace App\Controller;

use App\Telegram\Bot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\Types\Update;

class TelegramController extends AbstractController
{
    #[Route('/', name: 'app_telegram')]
    public function index(Request $request, Bot $bot): Response
    {
        $data   = $request->toArray();
        $update = Update::fromResponse($data);

        $bot->handle($update);

        return new Response();
    }
}
