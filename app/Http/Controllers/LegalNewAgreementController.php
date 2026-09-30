<?php

namespace App\Http\Controllers;

class LegalNewAgreementController extends Controller
{
    public function psmOla()
    {
        return view('pages.legal-new-agreement.placeholder', [
            'title' => 'PSM / OLA',
            'description' => 'PSM / OLA agreement requests will be managed here.',
        ]);
    }

    public function addendum()
    {
        return view('pages.legal-new-agreement.placeholder', [
            'title' => 'Addendum',
            'description' => 'Addendum agreement requests will be managed here.',
        ]);
    }

    public function others()
    {
        return view('pages.legal-new-agreement.placeholder', [
            'title' => 'Others',
            'description' => 'Other agreement requests will be managed here.',
        ]);
    }
}
