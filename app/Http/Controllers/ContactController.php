<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display the contact page.
     */
    public function index(): View
    {
        return view('contact', [
            'email' => config('contact.email'),
            'whatsapp' => config('contact.whatsapp'),
        ]);
    }
}
