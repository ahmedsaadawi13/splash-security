<?php
/**
 * Home Controller
 */
class HomeController extends Controller {

    public function index() {
        if ($this->user) {
            Redirect::to('/dashboard');
        }

        View::render('home/index', [
            'title' => 'Welcome to SplashSecurity'
        ]);
    }
}
