<?php

namespace App\Http\Controllers;
use App\Models\Asset;
use App\Models\LoanLog;
use App\Exports\LoanLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class LoanLogController extends Controller
{
    public function index()
    {
        $loans = LoanLog::latest('loan_date')->paginate(20);
        return view('loan-log.index', compact('loans'));
    }

    public function create()
    {
        return view('loan-log.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_date' => 'required|date',
            'loan_time' => 'nullable',
            'pic_user' => 'required|max:100',
            'item_description' => 'required|max:150',
            'quantity' => 'required|integer|min:1',
            'signature' => 'nullable'
        ]);

        LoanLog::create($validated);

        return redirect()->route('loan-log.index')
            ->with('success', 'Peminjaman berhasil dicatat');
    }

    public function show(LoanLog $loanLog)
    {
        return view('loan-log.show', compact('loanLog'));
    }

    public function update(Request $request, LoanLog $loanLog)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'status' => 'required|in:On Loan,Returned'
        ]);

        $loanLog->update($validated);

        return redirect()->route('loan-log.index')
            ->with('success', 'Status peminjaman berhasil diupdate');
    }

    public function export(Request $request)
    {
        $filters = [
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        return Excel::download(new LoanLogExport($filters), 'loan_log_' . date('Y-m-d') . '.xlsx');
    }
}
