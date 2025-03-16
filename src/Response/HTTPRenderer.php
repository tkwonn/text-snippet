<?php

namespace Response;

interface HTTPRenderer
{
    /**
     * @return array<string, string>
     */
    public function getFields(): array;

    public function getContent(): string;
}
