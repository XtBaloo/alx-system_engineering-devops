<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuardianController extends Controller
{
    public function index(Request $request)
    {
        $guardians = Guardian::withCount('students')
            ->when($request->search, function ($q, $search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('first_name')->paginate(20)->withQueryString();

        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('guardians.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $createLogin = $request->boolean('create_login');
        unset($data['create_login']);

        if ($createLogin && ! empty($data['email'])) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'password' => Hash::make(Str::random(12)),
            ]);
            $user->assignRole('parent');
            $data['user_id'] = $user->id;
        }

        $guardian = Guardian::create($data);

        return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian added.');
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students.currentClassArm.schoolClass');

        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $data = $this->validated($request);
        unset($data['create_login']);
        $guardian->update($data);

        return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian updated.');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        return redirect()->route('guardians.index')->with('success', 'Guardian removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'create_login' => ['boolean'],
        ]);
    }
}
