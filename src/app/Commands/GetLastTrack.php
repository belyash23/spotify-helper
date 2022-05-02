<?php

namespace App\Commands;

use App\Models\User;
use Illuminate\Support\Facades\App;
use SpotifyWebAPI\SpotifyWebAPI;
use WeStacks\TeleBot\Handlers\CommandHandler;

class GetLastTrack extends CommandHandler
{
    protected static $aliases = ['/last'];
    protected static $description = 'последний добавленный трек';

    public function handle()
    {
        $id = $this->update->user()->id;

        $api = App::make(SpotifyWebAPI::class, ['id' => $id]);

        $tracks = $api->getMySavedTracks(
            [
                'limit' => 1,
            ]
        );

        $this->sendMessage(
            [
                'text' => $tracks->items[0]->track->artists[0]->name . ' — ' . $tracks->items[0]->track->name
            ]
        );
    }
}
