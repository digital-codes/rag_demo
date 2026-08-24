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
        "messages" => $messages
    ];
    // with temperature for mistral.ai
    if (stripos($url, 'mistral') !== false) {
        $body['temperature'] = $temperature;
    }
    // with temperature and seed for deepinfra
    if (stripos($url, 'deepinfra') !== false) {
        $body['temperature'] = $temperature;
        $body['seed'] = $seed;
        $body['random_seed'] = $seed;
    }
    # no streaming fo rollama 
    if (stripos($url, 'ollama') !== false) {
        $body['stream'] = false;
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
        // ollama response is different
        if (stripos($url, 'ollama') !== false) {
            $reply = $result['message']['content'] ?? "No reply.";
        } else {
            $reply = $result['choices'][0]['message']['content'] ?? "No reply.";
            $impact = $result['impact'] ?? null;
            if ($impact) {
                $energy = $impact['energy'] ?? null;
                $co2 = $impact['emissions'] ?? null;
                if ($energy !== null && $co2 !== null) {
                    $impact = [
                        'energy' => $energy["total"] . " " . $energy["unit"],
                        'co2' => $co2["total"] . " " . $co2["unit"]
                    ];
                }
            }
        }
        $result = [
            'status' => 'ok',
            'reply' => $reply,
            'messages' => $messages,
            'impact' => $impact ?? null
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

