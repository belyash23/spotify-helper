<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Настройки</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="{{ asset('js/settings.js') }}" type="text/javascript"></script>
</head>
<body>
<div class="content" onload="alert()">
    @if (!$validated)
        <div class="not-validated">
            <object class="preloader" data="{{ asset('svg/preloader.svg') }}" type="image/svg+xml"></object>
            <div class="validating-error" hidden>Ошибка! Попробуйте перезайти в настройки.</div>
        </div>
    @else
        <form action="" class="settings-form" data-telegram-id= {{ $telegramId }}>

            <div class="set-default-playlist section">
                <label for="default-playlist" class="title set-default-playlist__label">Выберите плейлист по
                    умолчанию</label>
                <div class="description">Туда будут добавляться треки, не соответствующие остальным плейлистам</div>
                <select name="default-playlist" id="default-playlist" class="set-default-playlist__select">
                    @foreach ($playlists as $playlist)
                        <option value="{{ $playlist->id }}"
                                @selected($playlist->id == $defaultPlaylistId)>{{ $playlist->name }}</option>
                    @endforeach
                </select>
                <div class="use-default-playlist">
                    <input type="checkbox"
                           name="use-default-playlist"
                           id="use-default-playlist"
                           @checked(!is_null($defaultPlaylistId))>
                    <label for="use-default-playlist">Использовать плейлист по умолчанию</label>
                </div>
            </div>

            <div class="associate-playlists section">
                <div class="title">Установите соответствия между плейлистами и исполнителями</div>
                <div class="description">В плейлист будут добавляться треки указанных исполнителей</div>

                <table class="associate-playlists__table">
                    <thead>
                    <tr>
                        <th>Плейлист</th>
                        <th>Исполнитель</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($playlists as $playlist)
                        <tr class="playlist" data-id="{{ $playlist->id }}">
                            <td>{{ $playlist->name }}</td>
                            <td>
                                @foreach ($playlist->artists as $artist)
                                    <div class="playlist-artist">
                                        <input type="text" value="{{ $artist->name }}" class="playlist-artist__name"
                                               data-id="{{ $artist->spotify_id }}" data-text="{{ $artist->name }}">
                                        <button class="playlist-artist__remove" type="button">-</button>
                                    </div>
                                @endforeach
                                <button class="add-input add-artist" type="button">+</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="auto-creating-playlists section">
                <div class="title">Настройте автоматическое создание плейлистов</div>
                <div class="description">Укажите, при каком количестве треков с одинаковым исполнителем, не связанным с
                    существующим плейлистом, будет создаваться новый плейлист
                </div>

                <input type="number" class="min-tracks" value="{{ $minTracks ?? 1}}" min="1">

                <div class="use-auto-creating-playlists">
                    <input type="checkbox"
                           name="use-auto-creating-playlists"
                           id="use-auto-creating-playlists"
                           @checked(!is_null($minTracks))>
                    <label for="use-auto-creating-playlists">Создавать плейлисты автоматически</label>
                </div>
            </div>

            <div class="error" hidden>Ошибка! Проверьте введённые данные.</div>
            <input type="button" class="send" value="Сохранить настройки">
        </form>
    @endif
</div>
</body>
</html>
