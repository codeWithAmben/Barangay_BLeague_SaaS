<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function __construct(public TenantContext $tenantContext) {}

    /**
     * Ensure an active Barangay tenant is bound to the current request context.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->hasTenant()) {
            abort(403, 'No barangay league workspace is assigned to this account.');
        }

        return $next($request);
    }
}
