<?php

declare(strict_types=1);

namespace Webfloo\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;

abstract class FrontendController extends Controller
{
    /**
     * Abort with the branded frontend 404 page (no publish step needed —
     * hosts can still override everything via webfloo-error-pages tag).
     */
    protected function abortNotFound(): never
    {
        abort(response()->view('webfloo::frontend.errors.404', [], 404));
    }
}
