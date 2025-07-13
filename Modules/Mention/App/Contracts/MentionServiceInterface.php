<?php

namespace Modules\Mention\App\Contracts;

interface MentionServiceInterface
{
    public function getAll();
    public function find(string $id);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
}