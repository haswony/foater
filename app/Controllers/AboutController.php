<?php
namespace App\Controllers;

use App\Core\Controller;

class AboutController extends Controller
{
    public function developer(): void
    {
        $this->requireAuth();
        $this->view('about/developer', ['title' => 'عن المطور']);
    }
}
