<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;

class FeeCategoryController extends Controller
{
    public function index()
    {
        $categories = FeeCategory::withCount('feeStructures')->orderBy('name')->paginate(20);

        return view('fee-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('fee-categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        FeeCategory::create($data);

        return redirect()->route('fee-categories.index')->with('success', 'Fee category created.');
    }

    public function edit(FeeCategory $feeCategory)
    {
        return view('fee-categories.edit', compact('feeCategory'));
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $data = $this->validated($request);
        $feeCategory->update($data);

        return redirect()->route('fee-categories.index')->with('success', 'Fee category updated.');
    }

    public function destroy(FeeCategory $feeCategory)
    {
        if ($feeCategory->feeStructures()->exists()) {
            return back()->with('error', 'Cannot delete a category that has fee structures.');
        }

        $feeCategory->delete();

        return back()->with('success', 'Fee category removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:fee_categories,code,'.$request->route('fee_category')?->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
