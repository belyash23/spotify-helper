<?php

namespace App\Commands;

use App\Models\User;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Laravel\TeleBot;

class StartCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'начало работы';

    public function handle()
    {
        $this->createUser();
        $this->sendMessage(
            [
                'text' => 'Для использования бота необходимо авторизоваться в спотифае - /login'
            ]
        );
    }

    protected function createUser() {
        $id = $this->update->user()->id;
        User::create($id);
    }
}
