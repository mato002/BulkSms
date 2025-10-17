<?php

namespace App\Http\Controllers;

use App\Services\UrlShortenerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortLinkController extends Controller
{
    protected $urlShortener;

    public function __construct(UrlShortenerService $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

    /**
     * Handle short link redirect (e.g., /r/abc123)
     * This redirects to the reply form
     */
    public function redirect(string $code)
    {
        // Get message ID from short code
        $messageId = $this->urlShortener->getMessageIdFromCode($code);

        if (!$messageId) {
            abort(404, 'Invalid or expired link');
        }

        // Redirect to the reply form using the old token-based URL
        // We can keep using the PublicReplyController for the actual reply form
        $token = \App\Http\Controllers\PublicReplyController::encodeToken($messageId);
        
        return redirect()->route('public.reply', ['token' => $token]);
    }

    /**
     * Show analytics for a short link (optional - admin only)
     */
    public function analytics(string $code)
    {
        $analytics = $this->urlShortener->getAnalytics($code);

        if (!$analytics) {
            abort(404, 'Short link not found');
        }

        return response()->json($analytics);
    }
}


