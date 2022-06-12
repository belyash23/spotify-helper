<?php

namespace App\Commands;

use App\Models\User;
use Illuminate\Support\Facades\App;
use SpotifyWebAPI\SpotifyWebAPI;
use WeStacks\TeleBot\Handlers\CommandHandler;

class SettingsCommand extends CommandHandler
{
    protected static $aliases = ['/settings'];
    protected static $description = 'настройки';

    public function handle()
    {
        $id = $this->update->user()->id;

//        $api = App::make(SpotifyWebAPI::class, ['id' => $id]);

//        User::findByTelegramId($id)->associatePlaylists();

    }
}
