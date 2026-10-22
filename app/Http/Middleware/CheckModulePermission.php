<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('can.module:procurements,view')
 *        ->middleware('can.module:invoices,create')
 */
class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! PermissionService::userCan(auth()->user(), $module, $action)) {
            abort(403, 'Access denied — you do not have permission for this action.');
        }

        return $next($request);
    }
}
