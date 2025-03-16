<?php

namespace Controllers;

use Exception;
use Helpers\DatabaseHelper;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\JSONRenderer;

class PastesController
{
    /**
     * GET /api/pastes
     */
    public function index(): HTTPRenderer
    {
        try {
            $pastes = DatabaseHelper::findRecent();

            return new JSONRenderer($pastes);
        } catch (Exception $e) {
            return new JSONRenderer(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/pastes
     */
    public function store(): HTTPRenderer
    {
        $jsonString = file_get_contents('php://input');
        if ($jsonString === false) {
            return new JSONRenderer(['error' => 'Failed to read input']);
        }
        $paste = json_decode($jsonString, true);
        if (!is_array($paste)) {
            return new JSONRenderer(['error' => 'Invalid JSON data']);
        }

        $title = $paste['title'] ?? 'Untitled';
        $content = $paste['content'] ?? '';
        $language = $paste['language'] ?? 'plaintext';
        $isPublic = $paste['isPublic'] ?? 1;
        $expiresAt = $paste['expiresAt'] ?? 'Never';

        try {
            $result = DatabaseHelper::create(
                title:     $title,
                content:   $content,
                language:  $language,
                isPublic:  $isPublic,
                expiresAt: $expiresAt,
            );

            return new JSONRenderer(['hash' => $result['hash']]);
        } catch (Exception $e) {
            return new JSONRenderer(['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /{hash}
     */
    public function show(string $hash): HTTPRenderer
    {
        try {
            $paste = DatabaseHelper::findByHash($hash);

            if (isset($paste['expired']) && $paste['expired']) {
                return new HTMLRenderer('expired');
            }

            return new HTMLRenderer('paste', ['paste' => $paste]);
        } catch (Exception $e) {
            return new JSONRenderer(['error' => $e->getMessage()]);
        }
    }
}
