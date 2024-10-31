<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->scopes(['https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/spreadsheets'])->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $accessToken = $googleUser->token;

            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                Auth::login($user);
            } else {
                $user = new User();
                $user->name = $googleUser->name;
                $user->email = $googleUser->email;
                $user->password = bcrypt('password');
                $user->save();
                Auth::login($user);
            }

            Session::put('google_access_token', $accessToken);

            return redirect()->away('http://localhost:5173/login?token=' . $accessToken . '&user=' . urlencode(json_encode($user)));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to authenticate with Google.',
            ], 500);
        }
    }




    public function listGoogleSheets(Request $request)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $accessToken = substr($bearerToken, 7);

            if (!$accessToken) {
                return redirect()->route('login')->with('error', 'Google authentication required.');
            }
            $client = new Client();
            $client->setAccessToken($accessToken);

            $service = new Drive($client);
            $results = $service->files->listFiles([
                'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
            ]);

            $sheets = [];
            foreach ($results->getFiles() as $file) {
                $sheets[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                ];
            }
            return response()->json($sheets);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 401) {
                return response()->json(['message' => 'Invalid authentication credentials.'], 401);
            } else {
                return response()->json(['message' => 'An error occurred while fetching Google Sheets.'], 500);
            }
        }
    }
}
