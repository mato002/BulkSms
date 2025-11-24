<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if client accepts gzip/deflate
        if (!$request->header('Accept-Encoding') || 
            !str_contains($request->header('Accept-Encoding'), 'gzip')) {
            return $response;
        }

        // Only compress JSON and text responses
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json') && 
            !str_contains($contentType, 'text/')) {
            return $response;
        }

        // Don't compress if response is too small
        $content = $response->getContent();
        if (strlen($content) < 1024) { // Less than 1KB
            return $response;
        }

        // Compress the content
        $compressed = gzencode($content, 6); // Compression level 6 (balanced)

        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }
}




