<?php

namespace Response\Render;

use Response\HTTPRenderer;

class JSONRenderer implements HTTPRenderer
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
    }

    public function getContent(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
