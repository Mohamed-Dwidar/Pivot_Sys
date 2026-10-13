<?php

namespace Modules\PaymentModule\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\PaymentModule\app\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response;

// the Withholding Certificates pages: 404 while Settings › General "Withholding tax" is off (the certificates are kept)
class WithholdingEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(PaymentService::withholdingOn(), 404);

        return $next($request);
    }
}
