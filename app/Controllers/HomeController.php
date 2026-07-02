<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    /**
     * Display the main landing home page
     */
    public function index(): void
    {
        $this->render('home', [
            'title' => 'Elze.eg | Premium Egyptian Local Brand'
        ]);
    }
}
