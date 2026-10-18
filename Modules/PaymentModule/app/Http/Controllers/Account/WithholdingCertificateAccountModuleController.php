<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\CompanyModule\app\Services\CompanyService;
use Modules\PaymentModule\app\Http\Requests\WithholdingCertificateRequest;
use Modules\PaymentModule\app\Services\WithholdingCertificateService;
use Modules\ReservationModule\app\Models\Reservation;
use Yajra\DataTables\Facades\DataTables;

// logged in account: the account's withholding tax certificates (only the account manages them), attached to withholding payments
// create / edit / show open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class WithholdingCertificateAccountModuleController extends Controller
{
    private $certificateService;
    private $companyService;

    public function __construct(WithholdingCertificateService $certificateService, CompanyService $companyService)
    {
        $this->certificateService = $certificateService;
        $this->companyService = $companyService;
    }

    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function formOptions($certificate = null): array
    {
        $accountId = $this->accountId();
        return [
            // also the Member field's quick add companies (the member searches the server: member-select)
            'companies' => $this->companyService->options($accountId),
            'paymentOptions' => $this->certificateService->paymentOptions($accountId, $certificate),
        ];
    }

    public function index()
    {
        return view('paymentmodule::Withholding.index');
    }

    // DataTables server side: search (number, payer, tax id) + match / dates filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    private function table(array $filters, $id = null)
    {
        $query = $this->certificateService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['match', 'date_from', 'date_to'])))
            ->setRowId(fn ($certificate) => 'row-' . $certificate->id)
            ->addColumn('number_html', fn ($certificate) => '<a href="' . route('account.withholding-certificates.show', $certificate->id) . '" data-modal class="font-medium">' . e($certificate->certificate_no) . '</a>')
            ->editColumn('issued_at', fn ($certificate) => $certificate->issued_at?->format(Reservation::DATE_FORMAT))
            ->addColumn('payer', fn ($certificate) => e($certificate->payer_name) . ($certificate->payer_tax_id ? '<div class="text-xs text-slate-500">' . e($certificate->payer_tax_id) . '</div>' : ''))
            ->editColumn('amount', fn ($certificate) => number_format($certificate->amount, 2))
            ->addColumn('difference_html', fn ($certificate) => view('paymentmodule::Withholding.partials.difference', compact('certificate'))->render())
            ->addColumn('actions', fn ($certificate) => view('paymentmodule::Withholding.partials.row-actions', compact('certificate'))->render())
            ->rawColumns(['number_html', 'payer', 'difference_html', 'actions'])
            ->only(['DT_RowId', 'number_html', 'issued_at', 'payer', 'amount', 'payments_count', 'difference_html', 'actions']);
    }

    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('paymentmodule::Withholding.create', $this->formOptions());
    }

    public function store(WithholdingCertificateRequest $request)
    {
        $certificate = $this->certificateService->create($this->accountId(), $request->validated(), Auth::user());

        return response()->json([
            'message' => 'The certificate has been added successfully.',
            'reload' => true,
            'redirect' => route('account.withholding-certificates.show', $certificate->id),
        ]);
    }

    public function show($id)
    {
        $certificate = $this->certificateService->findOne($this->accountId(), $id);
        $hasFile = (bool) $this->certificateService->attachmentPath($certificate);
        return view('paymentmodule::Withholding.show', compact('certificate', 'hasFile'));
    }

    public function edit($id)
    {
        $certificate = $this->certificateService->findOne($this->accountId(), $id);
        return view('paymentmodule::Withholding.edit', compact('certificate') + $this->formOptions($certificate));
    }

    public function update(WithholdingCertificateRequest $request, $id)
    {
        $this->certificateService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The certificate has been updated successfully.',
            'row' => $this->row($request, $id),
            'modal' => route('account.withholding-certificates.show', $id),
            'redirect' => route('account.withholding-certificates.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->certificateService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The certificate has been deleted, its withholding entries can be attached again.',
            'row' => null,
            'redirect' => route('account.withholding-certificates.index'),
        ]);
    }

    // the attachment is a private file: only the certificate's account can download it
    public function attachment($id)
    {
        $certificate = $this->certificateService->findOne($this->accountId(), $id);
        $path = $this->certificateService->attachmentPath($certificate) ?? abort(404);

        return Storage::disk('local')->response($path, 'certificate-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $certificate->certificate_no) . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }
}
