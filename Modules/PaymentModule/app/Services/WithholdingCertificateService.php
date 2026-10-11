<?php

namespace Modules\PaymentModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\CompanyModule\app\Models\Company;
use Modules\MemberModule\app\Models\Member;
use Modules\PaymentModule\app\Models\Payment;
use Modules\PaymentModule\app\Models\WithholdingCertificate;
use Modules\PaymentModule\app\Repositories\WithholdingCertificateRepository;
use Modules\ReservationModule\app\Models\Reservation;

/**
 * Withholding tax certificates of an account (only the account manages them). Every method takes the owner account id.
 * A certificate covers withholding payments (payments.withholding_certificate_id); difference = its amount - their total
 * (shown, not blocking: a certificate can come before / after its payments are recorded).
 * The attachment is a private file (storage/app/private/withholding/{account_id}/), downloaded through the account.
 * Rule errors = ValidationException (also called outside the form later: imports).
 */
class WithholdingCertificateService
{
    private $certificateRepository;

    public function __construct(WithholdingCertificateRepository $certificateRepository)
    {
        $this->certificateRepository = $certificateRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->certificateRepository->forAccount($accountId)->filter($filters);
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id): WithholdingCertificate
    {
        return $this->certificateRepository->forAccount($accountId)->with('payments.member', 'creatable.userable')->findOrFail($id);
    }

    // the withholding payments that can be attached: not on another certificate (+ the ones of $certificate)
    // [id => "#100003 · 11-10-2026 · member · 25.00"]
    public function paymentOptions($accountId, ?WithholdingCertificate $certificate = null): array
    {
        return Payment::with('member')->where('account_id', $accountId)->where('type', 'withholding')
            ->where(fn ($query) => $query->where('withholding_certificate_id', 0)
                ->when($certificate, fn ($query) => $query->orWhere('withholding_certificate_id', $certificate->id)))
            ->orderByDesc('paid_at')->orderByDesc('payment_no')->get()
            ->mapWithKeys(fn ($payment) => [$payment->id => '#' . $payment->payment_no . ' · ' . $payment->paid_at?->format(Reservation::DATE_FORMAT)
                . ' · ' . ($payment->member?->name ?? '-') . ' · ' . number_format($payment->amount, 2)])->all();
    }

    /**
     * $data: certificate_no, issued_at, company_id, member_id, payer_name, payer_tax_id, period_from, period_to, amount, notes,
     *        payments (withholding payment ids), attachment (UploadedFile)
     */
    public function create($accountId, array $data, Model $creator): WithholdingCertificate
    {
        $this->assertData($accountId, $data);

        return DB::transaction(function () use ($accountId, $data, $creator) {
            $certificate = new WithholdingCertificate($this->certificateData($data) + ['account_id' => $accountId]);
            $certificate->creatable()->associate($creator);
            $certificate->save();
            $this->syncPayments($certificate, $data['payments'] ?? []);
            $this->storeAttachment($certificate, $data['attachment'] ?? null);

            return $certificate;
        });
    }

    // $data['delete_attachment']: remove the saved file (a new one replaces it anyway)
    public function update($accountId, $id, array $data): WithholdingCertificate
    {
        $certificate = $this->findOne($accountId, $id);
        $this->assertData($accountId, $data, $certificate);

        return DB::transaction(function () use ($certificate, $data) {
            $certificate->update($this->certificateData($data));
            $this->syncPayments($certificate, $data['payments'] ?? []);
            if (!empty($data['delete_attachment']) || !empty($data['attachment'])) {
                $this->deleteAttachment($certificate);
            }
            $this->storeAttachment($certificate, $data['attachment'] ?? null);

            return $certificate;
        });
    }

    // soft delete; its payments are free again (the file is kept with the deleted certificate)
    public function deleteOne($accountId, $id): void
    {
        $certificate = $this->findOne($accountId, $id);
        DB::transaction(function () use ($certificate) {
            Payment::where('withholding_certificate_id', $certificate->id)->update(['withholding_certificate_id' => 0]);
            $certificate->delete();
        });
    }

    // the private file path (download), null when there is none
    public function attachmentPath(WithholdingCertificate $certificate): ?string
    {
        $path = $certificate->attachment ? 'withholding/' . $certificate->account_id . '/' . $certificate->attachment : null;
        return $path && Storage::disk('local')->exists($path) ? $path : null;
    }

    // the payer belongs to the account; the payments are the account's free withholding payments
    private function assertData($accountId, array $data, ?WithholdingCertificate $certificate = null): void
    {
        if (!empty($data['company_id']) && !Company::where('account_id', $accountId)->whereKey((int) $data['company_id'])->exists()) {
            throw ValidationException::withMessages(['company_id' => 'This company is not one of yours.']);
        }
        if (!empty($data['member_id']) && !Member::where('account_id', $accountId)->whereKey((int) $data['member_id'])->exists()) {
            throw ValidationException::withMessages(['member_id' => 'This member is not one of yours.']);
        }
        if (round((float) ($data['amount'] ?? 0), 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be more than 0.']);
        }

        $ids = array_map('intval', $data['payments'] ?? []);
        $valid = array_keys($this->paymentOptions($accountId, $certificate));
        if (array_diff($ids, $valid)) {
            throw ValidationException::withMessages(['payments' => 'Choose withholding payments that are not on another certificate.']);
        }
    }

    private function certificateData(array $data): array
    {
        return [
            'certificate_no' => trim($data['certificate_no']),
            'issued_at' => $data['issued_at'],
            'company_id' => (int) ($data['company_id'] ?? 0),
            'member_id' => (int) ($data['member_id'] ?? 0),
            'payer_name' => trim($data['payer_name']),
            'payer_tax_id' => $data['payer_tax_id'] ?? null,
            'period_from' => $data['period_from'] ?? null,
            'period_to' => $data['period_to'] ?? null,
            'amount' => round((float) $data['amount'], 2),
            'notes' => $data['notes'] ?? null,
        ];
    }

    // the chosen payments point to the certificate, the ones removed are free again
    private function syncPayments(WithholdingCertificate $certificate, array $ids): void
    {
        $ids = array_map('intval', $ids);
        Payment::where('withholding_certificate_id', $certificate->id)->whereNotIn('id', $ids ?: [0])->update(['withholding_certificate_id' => 0]);
        if ($ids) {
            Payment::where('account_id', $certificate->account_id)->where('type', 'withholding')->whereIn('id', $ids)
                ->update(['withholding_certificate_id' => $certificate->id]);
        }
    }

    private function storeAttachment(WithholdingCertificate $certificate, ?UploadedFile $file): void
    {
        if (!$file) {
            return;
        }
        $name = Str::random(32) . '.' . strtolower($file->getClientOriginalExtension());
        $file->storeAs('withholding/' . $certificate->account_id, $name, 'local');
        $certificate->update(['attachment' => $name]);
    }

    private function deleteAttachment(WithholdingCertificate $certificate): void
    {
        if ($path = $this->attachmentPath($certificate)) {
            Storage::disk('local')->delete($path);
        }
        $certificate->update(['attachment' => null]);
    }
}
