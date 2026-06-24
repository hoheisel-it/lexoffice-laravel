<?php

namespace HoheiselIT\Lexoffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use HoheiselIT\Lexoffice\WebhookProcessor;

class WebhookController extends Controller
{
    public function __invoke(Request $request, WebhookProcessor $processor): \Illuminate\Http\JsonResponse
    {
        $payload = $request->json()->all();

        $processor->process($payload);

        return response()->json(['status' => 'ok']);
    }
}
