<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan alias middleware 'role'
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Error saat simpan, ubah, atau hapus data di semua fitur:
        // kembali ke halaman sebelumnya dengan pesan yang jelas.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->isMethod('GET')
                || $e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof HttpExceptionInterface) {
                return null;
            }

            $message = match (true) {
                $e instanceof ModelNotFoundException
                    => 'Data yang dimaksud tidak ditemukan. Data mungkin sudah dihapus. Silakan muat ulang halaman.',
                $e instanceof QueryException && str_starts_with((string) $e->getCode(), '23')
                    => 'Data tidak dapat diproses karena masih terhubung dengan data lain di sistem.',
                $e instanceof QueryException
                    => 'Data belum dapat diproses karena gangguan pada database. Silakan coba beberapa saat lagi. Jika masalah berlanjut, hubungi administrator.',
                $e instanceof \RuntimeException
                    => $e->getMessage(),
                default
                    => 'Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi. Jika masalah berlanjut, hubungi administrator.',
            };

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', $message);
        });
    })->create();