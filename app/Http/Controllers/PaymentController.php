<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentFee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['studentFee.student', 'studentFee.feeStructure.feeCategory', 'receivedBy'])
            ->when($request->search, function ($q, $search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('studentFee.student', fn ($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('admission_number', 'like', "%{$search}%"));
            })
            ->when($request->date, fn ($q, $date) => $q->whereDate('payment_date', $date))
            ->latest('id')
            ->paginate(25)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $student = $request->filled('student_id') ? Student::find($request->student_id) : null;
        $students = Student::active()->orderBy('first_name')->get();
        $studentFees = $student ? $student->studentFees()->outstanding()->with('feeStructure.feeCategory')->get() : collect();

        return view('payments.create', compact('students', 'student', 'studentFees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_fee_id' => ['required', 'exists:student_fees,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank_transfer,pos,other'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $studentFee = StudentFee::findOrFail($data['student_fee_id']);

        if ($data['amount'] > $studentFee->balance) {
            return back()->with('error', 'Payment amount exceeds the outstanding balance of ₦'.number_format($studentFee->balance, 2))->withInput();
        }

        $payment = DB::transaction(function () use ($data, $studentFee) {
            $payment = Payment::create([
                'student_fee_id' => $studentFee->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'received_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $studentFee->amount_paid = round((float) $studentFee->amount_paid + (float) $data['amount'], 2);
            $studentFee->status = $studentFee->amount_paid >= $studentFee->amount_due ? 'paid' : 'partial';
            $studentFee->save();

            return $payment;
        });

        return redirect()->route('payments.receipt', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['studentFee.student', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        $settings = \App\Models\SchoolSetting::current();

        return view('payments.receipt', compact('payment', 'settings'));
    }

    public function receiptPdf(Payment $payment)
    {
        $payment->load(['studentFee.student', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        $settings = \App\Models\SchoolSetting::current();

        $pdf = Pdf::loadView('payments.receipt-pdf', compact('payment', 'settings'))->setPaper('a5', 'portrait');

        return $pdf->download("Receipt-{$payment->receipt_number}.pdf");
    }
}
