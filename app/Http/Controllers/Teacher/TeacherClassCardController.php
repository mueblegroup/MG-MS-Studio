<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassCard;
use Illuminate\Http\Request;

class TeacherClassCardController extends Controller
{
    public function index()
    {
        $classCards = ClassCard::query()
            ->orderByDesc('id')
            ->paginate(12);

        return view('teacher.classcards.index', compact('classCards'));
    }

    public function show(ClassCard $classCard)
    {
        // show purchases (user_class_cards)
        $classCard->load([
            'purchases.user' // requires relations in models (see note below)
        ]);

        return view('teacher.classcards.show', compact('classCard'));
    }
}