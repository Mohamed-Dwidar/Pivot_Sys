<?php

namespace Modules\PaymentModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\MemberModule\app\Services\MemberService;
use Modules\PaymentModule\app\Http\Requests\PaymentRequest;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Services\IncomeCategoryService;
use Modules\PaymentModule\app\Services\PaymentMethodService;
use Modules\PaymentModule\app\Services\PaymentService;
use Modules\ReservationModule\app\Models\Reservation;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Yajra\DataTables\Facades\DataTables;

/**
 * Payments actions shared by the account and the employee controllers (both add and view the account's payments;
 * edit / delete / refund = the account only, Payments phase 1 step 6).
 * The controller gives area(): "account" | "employee" (route names {area}.payments.*), the views read it from the route.
 * show / create open in the popup (ajax), store answers JSON (custom.js ajax forms), receipt() is the PDF,
 * payables() fills the reservation / package drop menu of the form (custom.js [data-options-url]).
 */
trait PaymentActions
{
    private PaymentService $paymentService;
    private PaymentMethodService $methodService;
    private IncomeCategoryService $categoryService;
    private MemberService $memberService;

    abstract protected function area(): string;

    public function __construct(PaymentService $paymentService, PaymentMethodService $methodService, IncomeCategoryService $categoryService, MemberService $memberService)
    {
        $this->paymentService = $paymentService;
        $this->methodService = $methodService;
        $this->categoryService = $categoryService;
        $this->memberService = $memberService;
    }

    // the account itself, or the account of the logged in employee
    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function route($name, $parameters = [])
    {
        return route($this->area() . '.payments.' . $name, $parameters);
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        $accountId = $this->accountId();
        return view('paymentmodule::Payment.index', [
            'methods' => $this->methodService->filterOptions($accountId),
            'categories' => $this->categoryService->filterOptions($accountId),
        ]);
    }

    // DataTables server side: search (number, reference, member) + for / type / method / category / dates filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row(), steps 6+)
    private function table(array $filters, $id = null)
    {
        $query = $this->paymentService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the payments
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]
                + Arr::only($filters, ['source', 'type', 'payment_method_id', 'income_category_id', 'date_from', 'date_to'])))
            ->setRowId(fn ($payment) => 'row-' . $payment->id)
            ->orderColumn('paid_at', fn ($query, $order) => $query->orderBy('paid_at', $order)->orderBy('payment_no', $order))
            ->addColumn('number_html', fn ($payment) => '<a href="' . $this->route('show', $payment->id) . '" data-modal class="font-medium">#' . $payment->payment_no . '</a>')
            ->editColumn('paid_at', fn ($payment) => $payment->paid_at?->format(Reservation::DATE_FORMAT))
            ->addColumn('member', fn ($payment) => e($payment->member?->name ?? '-'))
            ->addColumn('source', fn ($payment) => view('paymentmodule::Payment.partials.row-source', compact('payment'))->render())
            ->addColumn('type_html', fn ($payment) => '<span class="badge ' . $payment->type_badge_class . '">' . e($payment->type_label) . '</span>')
            ->addColumn('method', fn ($payment) => e($payment->paymentMethod?->name ?? '-'))
            ->editColumn('amount', fn ($payment) => number_format($payment->signed_amount, 2))
            ->addColumn('actions', fn ($payment) => view('paymentmodule::Payment.partials.row-actions', compact('payment'))->render())
            ->rawColumns(['number_html', 'source', 'type_html', 'actions'])
            ->only(['DT_RowId', 'number_html', 'paid_at', 'member', 'source', 'type_html', 'method', 'amount', 'actions']);
    }

    // the member's reservations / packages (json [{id, name}]) for the form: ?member_id=&payable=reservation|package
    public function payables(Request $request)
    {
        $kind = (string) $request->input('payable');
        if (!isset(PaymentService::PAYABLES[$kind])) {
            return response()->json([]);
        }

        return response()->json($this->paymentService->payableOptions($this->accountId(), $kind, (int) $request->input('member_id')));
    }

    // ?payable=reservation|package&payable_id=X: the form opens for it (the "Add payment" of a reservation / package view)
    public function create(Request $request)
    {
        $accountId = $this->accountId();
        $payment = new Payment(['type' => 'payment', 'paid_at' => today()]);
        // reservation | package | '' (other income)
        $payableKind = 'reservation';
        $payableOptions = [];

        if (isset(PaymentService::PAYABLES[$request->input('payable')]) && $request->filled('payable_id')) {
            $payableKind = $request->input('payable');
            $payable = $this->paymentService->findPayable($accountId, $payableKind, (int) $request->input('payable_id'));
            $payment->payable_id = $payable->getKey();
            $payment->member_id = $payable->payableMemberId();
            // the amount still to pay (nothing when it is paid / a credit)
            $payment->amount = max(0, (float) $payable->remaining) ?: null;
            $payableOptions = $this->paymentService->payableOptions($accountId, $payableKind, $payment->member_id);
        }

        return view('paymentmodule::Payment.create', [
            'payment' => $payment,
            'payableKind' => $payableKind,
            'payableOptions' => collect($payableOptions)->pluck('name', 'id')->all(),
            'members' => $this->memberService->options($accountId),
            'methods' => $this->methodService->options($accountId),
            'categories' => $this->categoryService->options($accountId),
        ]);
    }

    public function store(PaymentRequest $request)
    {
        $payment = $this->paymentService->create($this->accountId(), $request->validated(), Auth::user());

        return response()->json([
            'message' => 'Payment #' . $payment->payment_no . ' has been added successfully.',
            'reload' => true,
            'redirect' => $this->route('show', $payment->id),
        ]);
    }

    public function show($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        $payableName = $this->paymentService->payableName($payment->payable);
        return view('paymentmodule::Payment.show', compact('payment', 'payableName'));
    }

    // the receipt (PDF, opened in the browser)
    public function receipt($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        $payableName = $this->paymentService->payableName($payment->payable);
        $account = $payment->account;

        // the Cairo font of config/pdf.php when its files are there, else mpdf's DejaVu Sans (Arabic too)
        $config = is_file(config('pdf.font_path') . 'Cairo-Regular.ttf') ? [] : ['default_font' => 'dejavusans'];
        $config += ['format' => 'A5'];

        // a normal response (the package's stream() echoes it itself, outside the Laravel response)
        $pdf = Pdf::loadView('paymentmodule::Payment.receipt-pdf', compact('payment', 'payableName', 'account'), [], $config)->output();
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipt-' . $payment->payment_no . '.pdf"',
        ]);
    }
}
