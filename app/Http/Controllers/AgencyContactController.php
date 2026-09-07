<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgencyContactRequest;
use App\Models\AgencyContact;
use App\Models\Boutique;
use App\Models\Bureau;
use App\Models\Parcelle;
use App\Models\Property;
use App\Models\User;
use App\Notifications\AgencyContactNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class AgencyContactController extends Controller
{
    public function propertyCreate(Property $property)
    {
        return view('pages.agency-contact.create', [
            'contactable' => $property,
            'type' => 'property',
        ]);
    }

    public function propertyStore(StoreAgencyContactRequest $request, Property $property)
    {
        return $this->processContact($request, $property, 'property.show');
    }

    public function commercialPropertyCreate(Property $property, string $type)
    public function boutiqueCreate(Boutique $boutique)
    {
        abort_unless($property->property_type?->value === $type, 404);

        return view('pages.agency-contact.create', [
            'contactable' => $property,
            'type' => $type,
            'contactable' => $boutique,
            'type' => 'boutique',
        ]);
    }

    public function commercialPropertyStore(StoreAgencyContactRequest $request, Property $property, string $type)
    public function boutiqueStore(StoreAgencyContactRequest $request, Boutique $boutique)
    {
        abort_unless($property->property_type?->value === $type, 404);
        return $this->processContact($request, $boutique, 'boutique.show');
    }

        return $this->processContact($request, $property, "{$type}.show");
    public function bureauCreate(Bureau $bureau)
    {
        return view('pages.agency-contact.create', [
            'contactable' => $bureau,
            'type' => 'bureau',
        ]);
    }

    public function bureauStore(StoreAgencyContactRequest $request, Bureau $bureau)
    {
        return $this->processContact($request, $bureau, 'bureau.show');
    }

    public function parcelleCreate(Parcelle $parcelle)
    {
        return view('pages.agency-contact.create', [
            'contactable' => $parcelle,
            'type' => 'parcelle',
        ]);
    }

    public function parcelleStore(StoreAgencyContactRequest $request, Parcelle $parcelle)
    {
        return $this->processContact($request, $parcelle, 'parcelles.show');
    }

    protected function processContact(StoreAgencyContactRequest $request, Property|Parcelle $contactable, string $routeName)
    protected function processContact(StoreAgencyContactRequest $request, Model $contactable, string $routeName)
    {
        $contact = AgencyContact::create([
            'contactable_id' => $contactable->id,
            'contactable_type' => $contactable::class,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $users = User::permission('manage-properties')->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new AgencyContactNotification($contact));
        }

        return redirect()->route($routeName, $contactable)
            ->with('success', 'Votre message a été envoyé à l\'entreprise. Nous vous répondrons dans les plus brefs délais.');
    }
}
