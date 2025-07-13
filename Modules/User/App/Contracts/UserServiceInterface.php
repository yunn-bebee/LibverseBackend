<?php

namespace Modules\User\App\Contracts;

interface UserServiceInterface {
    public function get($id);
    public function getAll();
    public function save();
    public function update();
    public function delete($id);
}
