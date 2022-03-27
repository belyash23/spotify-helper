<?php

namespace App\Commands;

use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'начало работы';

    public function handle()
    {
        $this->sendMessage(
            [
                'text' => 'Для использования бота необходимо авторизоваться в спотифае'
            ]
        );
    }
}
