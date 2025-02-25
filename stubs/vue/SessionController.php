<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('settings/Sessions', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        if (config('session.driver') !== 'database') {
            return back();
        }

        Auth::logoutOtherDevices($request->password);

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        request()->session()->put([
            'password_hash_'.Auth::getDefaultDriver() => $request->user()->getAuthPassword(),
        ]);

        return back();
    }
}
