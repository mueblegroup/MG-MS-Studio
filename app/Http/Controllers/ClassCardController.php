<?php

namespace App\Http\Controllers;

use App\Models\ClassCard;
use App\Models\UserClassCard;
use App\Models\User;
use App\Models\Plan;
use App\Models\PlanSession;

use Illuminate\Http\Request;

class ClassCardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $cards = ClassCard::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classcards.index', compact('cards', 'search', 'perPage'));
    }

    public function create()
    {
        return view('admin.classcards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_classes' => 'required|integer|min:1|max:1000',
            'validity_weeks' => 'required|integer|min:1|max:520',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        ClassCard::create([
            'name' => $validated['name'],
            'total_classes' => $validated['total_classes'],
            'validity_weeks' => $validated['validity_weeks'],
            'price' => $validated['price'],
            'is_active' => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.classcards.index')->with('success', 'Class card created.');
    }

    public function show(\App\Models\ClassCard $classcard)
    {
        $classcard->load([
            'purchases.user',
        ]);

        return view('admin.classcards.show', [
            'classCard' => $classcard,
        ]);
    }



    public function edit(ClassCard $classcard)
    {
        return view('admin.classcards.edit', ['card' => $classcard]);
    }

    public function update(Request $request, ClassCard $classcard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_classes' => 'required|integer|min:1|max:1000',
            'validity_weeks' => 'required|integer|min:1|max:520',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $classcard->update([
            'name' => $validated['name'],
            'total_classes' => $validated['total_classes'],
            'validity_weeks' => $validated['validity_weeks'],
            'price' => $validated['price'],
            'is_active' => (bool)($validated['is_active'] ?? false),
        ]);

        return redirect()->route('admin.classcards.index')->with('success', 'Class card updated.');
    }

    public function destroy(ClassCard $classcard)
    {
        $classcard->delete();

        return redirect()->route('admin.classcards.index')->with('success', 'Class card deleted.');
    }
}
