{{-- certificate amount - its withholding payments: 0 = matches (green), else the difference (amber; + = payments missing) --}}
@php($difference = $certificate->difference())
@if ($difference == 0)
    <span class="badge badge-status-success">Matches</span>
@else
    <span class="badge badge-status-warning" title="{{ $difference > 0 ? 'The certificate is more than its payments' : 'The payments are more than the certificate' }}">{{ $difference > 0 ? '+' : '' }}{{ number_format($difference, 2) }}</span>
@endif
