<x-layouts.dashboard title="Record Payment">
    <x-card class="mb-6">
        <form method="GET">
            <label class="form-label">Select Student</label>
            <select name="student_id" class="form-select w-72" onchange="this.form.submit()">
                <option value="">-- Select student --</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" @selected($student?->id == $s->id)>{{ $s->full_name }} ({{ $s->admission_number }})</option>
                @endforeach
            </select>
        </form>
    </x-card>

    @if($student)
        <x-card :title="'Outstanding fees for '.$student->full_name">
            @if($studentFees->isEmpty())
                <x-empty-state title="No outstanding fees for this student" />
            @else
                <form method="POST" action="{{ route('payments.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">Fee</label>
                        <select name="student_fee_id" class="form-select" required>
                            @foreach($studentFees as $fee)
                                <option value="{{ $fee->id }}">{{ $fee->feeStructure->feeCategory->name }} ({{ $fee->term->name }}) — Balance: ₦{{ number_format($fee->balance, 2) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('student_fee_id')" class="mt-1" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="form-label">Amount (₦)</label>
                            <input type="number" step="0.01" name="amount" class="form-input" required>
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                        <div>
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="pos">POS</option>
                                <option value="other">Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
                        </div>
                        <div>
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-input" required>
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-1" />
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-textarea"></textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Record Payment</button>
                    </div>
                </form>
            @endif
        </x-card>
    @endif
</x-layouts.dashboard>
