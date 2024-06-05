<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Initialize a Guzzle HTTP client
$client = new Client();

// API URL for chat completions
$apiUrl = 'https://api.openai.com/v1/chat/completions';
$apiKey = $_ENV['CHATGPT_API_KEY'];

try {
    // Check if there's a POST request to send a message
    if ($isPost && isset($params->message)) {
        $userMessage = $params->message;

        // Sending a POST request to the AI API
        $response = $client->request('POST', $apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $userMessage]],
                'max_tokens' => 500,
            ],
        ]);

        // Get the body of the response
        $responseBody = $response->getBody();
        $responseContent = json_decode($responseBody, true);
        $aiMessage = $responseContent['choices'][0]['message']['content'];

        // Detect if the response contains code
        $hasCode = preg_match('/```/', $aiMessage);

        // Escape the AI message content
        $escapedMessage = htmlspecialchars($aiMessage);

        // If the response contains code, wrap it in <pre> and <code> tags
        if ($hasCode) {
            $escapedMessage = '<pre><code>' . $escapedMessage . '</code></pre>';
        }
        renderMessage($userMessage, $escapedMessage);
        exit;
    }
} catch (RequestException $e) {
    echo "API request failed: " . $e->getMessage();
}

?>

<div class="w-screen h-screen grid place-items-center">
    <div class="w-96 mx-auto my-10 p-5 border rounded shadow">
        <h1 class="text-2xl mb-4">Chat with AI</h1>
        <div id="chat-container" class="space-y-2 mb-4 max-h-96 overflow-auto">
            <?php function renderMessage($userMessage, $escapedMessage)
            { ?>
                <div class="chat chat-end">
                    <div class="chat-bubble chat-bubble-neutral/20">
                        <?= htmlspecialchars($userMessage) ?>
                    </div>
                </div>
                <div class="chat chat-start">
                    <div class="chat-bubble chat-bubble-neutral/20 overflow-x-auto">
                        <?= $escapedMessage ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <form class="flex flex-col gap-4" 
        hx-post="<?= $pathname ?>" 
        hx-swap="beforeend scroll:bottom" 
        hx-target="#chat-container" 
        hx-trigger="submit" 
        hx-vals='{"attributes": [{"id":"message", "attributes": {"add":"readonly"}}, {"id":"send", "attributes": {"add":"disabled"}}, {"id":"loading", "attributes": {"class":"-hidden block"}}], 
        "swaps": [{"id":"message", "attributes": {"remove":"readonly"}}, {"id":"send", "attributes": {"remove":"disabled"}}, {"id":"loading", "attributes": {"class":"-block hidden"}}]}' hx-form="afterRequest:reset">
            <input id="message" type="text" name="message" placeholder="Type a message..." class="border rounded p-2 w-full" required>
            <button id="send" class="btn btn-primary">
                <span id="loading" class="loading loading-spinner hidden"></span>
                Send
            </button>
        </form>
    </div>
</div>