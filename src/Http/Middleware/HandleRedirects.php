<?php

declare(strict_types=1);

namespace Webfloo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webfloo\Models\Redirect;

class HandleRedirects
{
    /**
     * Rescue only requests nothing else handled: the lookup runs solely on
     * GET 404 responses, so live content at the same path always wins and
     * stale redirects can never shadow a page.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404 || ! $request->isMethod('GET')) {
            return $response;
        }

        $redirect = Redirect::forPath($request->path());

        if ($redirect === null) {
            return $response;
        }

        $redirect->increment('hits_count');

        $target = $redirect->to_path;

        // Carry the query string over (pagination, UTM tags).
        if (is_string($request->getQueryString()) && $request->getQueryString() !== '') {
            $target .= (str_contains($target, '?') ? '&' : '?').$request->getQueryString();
        }

        return redirect($target, $redirect->status_code);
    }
}
