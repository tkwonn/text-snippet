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
        $paste = json_decode(file_get_contents('php://input'), true);
        if (!$paste) {
            return new JSONRenderer(['error' => 'Invalid JSON data']);
        }

        try {
            $result = DatabaseHelper::create(
                content: $paste['content'],
                title: $paste['title'] ?? 'Untitled',
                language: $paste['language'] ?? 'plaintext',
                isPublic: $paste['isPublic'] ?? 1,
                expiresAt: $paste['expiresAt'] ?? 'Never',
            );

            return new JSONRenderer(
                ['hash' => $result['hash']],
            );
        } catch (Exception $e) {
            return new JSONRenderer(
                ['error' => $e->getMessage()],
            );
        }
    }

    /**
     * GET /{hash}
     */
    public function show(string $hash): HTTPRenderer
    {
        $paste = DatabaseHelper::findByHash($hash);

        if (isset($paste['expired']) && $paste['expired']) {
            return new HTMLRenderer('expired');
        }

        return new HTMLRenderer('paste', ['paste' => $paste]);
    }
}
