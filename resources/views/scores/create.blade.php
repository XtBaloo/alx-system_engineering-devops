<x-layouts.dashboard title="Enter Scores" :subtitle="$term?->name">
    <x-card class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Class</label>
                <select name="class_arm_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">-- Select class --</option>
                    @foreach($classArms as $arm)
                        <option value="{{ $arm->id }}" @selected($classArmId == $arm->id)>{{ $arm->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select w-56" onchange="this.form.submit()">
                    <option value="">-- Select subject --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected($subjectId == $subj->id)>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>

    @if(!$session || !$term)
        <x-empty-state title="No active academic session/term" />
    @elseif($classArmId && $subjectId)
        <x-card>
            <form method="POST" action="{{ route('scores.store') }}">
                @csrf
                <input type="hidden" name="class_arm_id" value="{{ $classArmId }}">
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Student</th>
                                @foreach($assessmentTypes as $type)
                                    <th>{{ $type->name }} (/{{ $type->max_score }})</th>
                                @endforeach
                                <th>Exam (/{{ \App\Models\SchoolSetting::current()->examination_max_score }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $s)
                                <tr>
                                    <td class="font-medium">{{ $s->full_name }}</td>
                                    @foreach($assessmentTypes as $type)
                                        <td>
                                            <input type="number" step="0.01" min="0" max="{{ $type->max_score }}"
                                                name="assessments[{{ $s->id }}][{{ $type->id }}]"
                                                value="{{ old('assessments.'.$s->id.'.'.$type->id, optional($existingAssessments->get($s->id)?->get($type->id))->first()?->score) }}"
                                                class="form-input w-20 @error('assessments.'.$s->id.'.'.$type->id) border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                            @error('assessments.'.$s->id.'.'.$type->id)
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                    @endforeach
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ \App\Models\SchoolSetting::current()->examination_max_score }}"
                                            name="examinations[{{ $s->id }}]"
                                            value="{{ old('examinations.'.$s->id, $existingExams->get($s->id)?->score) }}"
                                            class="form-input w-24 @error('examinations.'.$s->id) border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                        @error('examinations.'.$s->id)
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $assessmentTypes->count() + 2 }}"><x-empty-state title="No active students in this class" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->isNotEmpty())
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary">Save Scores</button>
                </div>
                @endif
            </form>
        </x-card>
    @else
        <x-empty-state title="Select a class and subject to begin" />
    @endif
</x-layouts.dashboard>
