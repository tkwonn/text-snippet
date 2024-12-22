<?php

use Helpers\DatabaseHelper;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\JSONRenderer;

return [
    '' => function (): HTTPRenderer {
        return new HTMLRenderer('home');
    },
    'api/paste' => function (): HTTPRenderer {
        $rawPostData = file_get_contents('php://input');
        $data = json_decode($rawPostData, true);
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        try {
            $paste = DatabaseHelper::createPaste(
                content: $data['content'],
                title: $data['title'],
                language: $data['language'],
                expiresAt: $data['expiresAt'],
                exposure: $data['exposure'],
            );

            return new JSONRenderer([
                'hash_id' => $paste['hash_id'],
            ]);
        } catch (Exception $e) {
            return new JSONRenderer(['error' => $e->getMessage()]);
        }
    },
    'api/recent-pastes' => function (): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('Request method must be GET');
        }

        try {
            $recentPastes = DatabaseHelper::getRecentPublicPastes();

            return new JSONRenderer($recentPastes);
        } catch (Exception $e) {
            return new JSONRenderer(['error' => $e->getMessage()]);
        }
    },
    '[a-zA-Z0-9_-]{8}' => function (string $hashId): HTTPRenderer {
        try {
            $paste = DatabaseHelper::getPasteByHashId($hashId);

            if (isset($paste['expired']) && $paste['expired']) {
                return new HTMLRenderer('expired');
            }

            return new HTMLRenderer('paste', ['paste' => $paste]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    },
];
