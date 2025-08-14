<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DashboardCard extends Component
{
    public $title;
    public $count;
    public $icon;
    public $bg;

    public function __construct($title, $count, $icon, $bg)
    {
        $this->title = $title;
        $this->count = $count;
        $this->icon = $icon;
        $this->bg = $bg;
    }

    public function render()
    {
        return view('components.dashboard-card'); // Blade komponen
    }
}
