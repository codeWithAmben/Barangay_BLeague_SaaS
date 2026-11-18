<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromRoute
{
    public function __construct(public TenantContext $tenantContext) {}

    /**
     * Bind the public portal route's Tenant model into the TenantContext.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if ($tenant instanceof Tenant) {
            $this->tenantContext->set($tenant);
        }

        try {
            return $next($request);
        } finally {
            $this->tenantContext->clear();
        }
    }
}
