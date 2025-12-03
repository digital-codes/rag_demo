<?php

function remoteQuery($key, $model, $url, $prompt, $query, $temperature = 0.5, $seed = 1234): array
{
    // Build the messages array
    $messages = [
        ["role" => "system", "content" => $prompt]
    ];

    // Add the current user query
    // In PHP, using $array[] = $value appends $value at the next numeric index.
    $messages[] = ["role" => "user", "content" => $query];

    // Prepare request payload. mistral.ai doesn't support 'seed'
    $body = [
        "model" => $model,
        "messages" => $messages,
        "temperature" => $temperature,
    ];
    if (stripos($url, 'mistral.ai') === false) {
        $body['seed'] = $seed;
    }
    $payload = json_encode($body);

    // Set up cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);        // 180s overall limit
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $key"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Execute and get the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle the response
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $reply = $result['choices'][0]['message']['content'] ?? "No reply.";
        $result = [
            'status' => 'ok',
            'reply' => $reply,
            'messages' => $messages
        ];
    } else {
        $result = [
            'status' => 'error',
            'reply' => "Error ($httpCode): $response",
            'messages' => $messages
        ];
    }
    return $result;
}

