<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\User;
use App\Notifications\ContactFormSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
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

    /**
     * Store a new contact form submission.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $admins = User::role('super-admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new ContactFormSubmitted($contact));
        }

        return redirect()->route('contact')
            ->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
    }
}
