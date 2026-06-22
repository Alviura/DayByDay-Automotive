<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayrollPeriodRequest;
use App\Models\Employee;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $payrollService
    ) {
        $this->middleware('permission:payroll.view')->only(['index', 'showPeriod', 'payslip']);
        $this->middleware('permission:payroll.run')->only(['storePeriod', 'generate']);
        $this->middleware('permission:payroll.lock')->only(['lock', 'markPaid']);
    }

    public function index(Request $request): View
    {
        $periods = PayrollPeriod::with('latestRun')
            ->when($request->year, fn ($q) => $q->where('year', $request->year))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'employees' => Employee::onPayroll()->count(),
            'periods' => PayrollPeriod::count(),
            'pending' => PayrollPeriod::whereIn('status', ['draft', 'calculated'])->count(),
            'paid' => PayrollPeriod::where('status', 'paid')->count(),
        ];

        $years = PayrollPeriod::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('payroll.index', compact('periods', 'stats', 'years'));
    }

    public function storePeriod(StorePayrollPeriodRequest $request): RedirectResponse
    {
        $period = $this->payrollService->createPeriod(
            (int) $request->year,
            (int) $request->month
        );

        return redirect()->route('payroll.periods.show', $period)
            ->with('status', 'Payroll period created.');
    }

    public function showPeriod(PayrollPeriod $period): View
    {
        $period->load(['runs.processor', 'runs.locker', 'latestRun.lines.employee']);

        return view('payroll.show', compact('period'));
    }

    public function generate(PayrollPeriod $period): RedirectResponse
    {
        try {
            $run = $this->payrollService->generateRun($period, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payroll.periods.show', $period)
            ->with('status', "Payroll run {$run->run_number} generated for {$period->label()}.");
    }

    public function lock(PayrollRun $run): RedirectResponse
    {
        try {
            $this->payrollService->lockRun($run, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Payroll run locked.');
    }

    public function markPaid(PayrollRun $run): RedirectResponse
    {
        try {
            $this->payrollService->markPaid($run);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Payroll marked as paid.');
    }

    public function payslip(PayrollLine $line): View
    {
        $user = auth()->user();

        if ($user->can('payroll.view')) {
            // HR / admin can view any payslip
        } elseif ($user->can('payslips.view_own')) {
            $employee = Employee::where('user_id', $user->id)->first();
            if (! $employee || $line->employee_id !== $employee->id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $line->load(['employee', 'run.period']);

        return view('payroll.payslip', compact('line'));
    }
}
