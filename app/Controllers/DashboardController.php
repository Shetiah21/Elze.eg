<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class DashboardController extends Controller
{
    private Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * Display the User Account Dashboard
     */
    public function index(): void
    {
        // 1. Guard route: redirect to login if session is empty
        $user = $this->session->get('user');
        if (!$user) {
            $this->session->setFlash('error', 'You must be logged in to access your dashboard.');
            $this->redirect('/login');
        }

        // 2. Render dashboard index
        $this->render('dashboard/index', [
            'title' => 'My Account | Elze.eg',
            'user' => $user,
            'active_tab' => 'profile'
        ]);
    }
}
