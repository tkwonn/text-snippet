<?php

namespace Response\Render;

use Response\HTTPRenderer;

class HTMLRenderer implements HTTPRenderer
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private string $viewFile,
        private array $data = []
    ) {
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => 'text/html; charset=UTF-8',
        ];
    }

    public function getContent(): string
    {
        $viewPath = $this->getViewPath($this->viewFile);

        ob_start();
        extract($this->data);
        require $viewPath;

        $content = ob_get_clean();
        if ($content === false) {
            throw new \RuntimeException('Failed to render the view file.');
        }

        return $content;
    }

    private function getViewPath(string $path): string
    {
        return sprintf('%s/../../Views/%s.php', __DIR__, $path);
    }
}
