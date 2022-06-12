<?php

namespace App\Views;

use Illuminate\View\View;

class SettingsComposer
{
    public function compose(View $view)
    {
        $validated = false;

        $existingData = $view->getData();
        extract($existingData);

        $view->with('validated', $validated);
    }
}
