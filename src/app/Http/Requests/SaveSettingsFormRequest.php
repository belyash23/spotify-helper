<?php

namespace App\Http\Requests;


class SaveSettingsFormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'telegramId' => ['integer', 'required'],
            'settings.defaultPlaylistId' => ['integer', 'nullable'],
            'settings.playlists' => 'array',
            'settings.playlists.*.id' => 'integer',
            'settings.playlists.*.artists' => 'array',
            'settings.playlists.*.artists.id' => 'string',
            'settings.playlists.*.artists.name' => 'string',
            'settings.minTracksCount' => ['integer', 'nullable']
        ];
    }
}
