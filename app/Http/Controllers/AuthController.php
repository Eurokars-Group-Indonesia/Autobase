<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $userEmail = Auth::user()->email;
        
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();
        
        // Log logout
        Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $userEmail,
            'ip' => $request->ip()
        ]);

        // Note: We only logout from the application, not from Azure AD
        // This prevents logging out users from all Microsoft services (Outlook, Teams, etc.)
        return redirect()->route('login');
    }

    /**
     * Redirect to Azure SSO
     */
    
        public function redirectToAzure()
        {
            $tenant = config('services.azure.tenant');
            $clientId = config('services.azure.client_id');
            
            Log::info('Azure SSO Redirect initiated', [
                'tenant' => $tenant,
                'client_id' => $clientId,
                'redirect_uri' => config('services.azure.redirect'),
                'expected_auth_url' => "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize"
            ]);
            
            // Use only user-consentable scopes (remove User.Read.All which requires admin consent)
            return Socialite::driver('azure')
                ->scopes(['openid', 'profile', 'email', 'User.Read', 'offline_access'])
                ->redirect();
        }


    /**
     * Handle Azure SSO callback
     */
    public function handleAzureCallback(Request $request)
    {
        try {
            // Check if there's an error from Azure
            if ($request->has('error')) {
                Log::error('Azure SSO error response', [
                    'error' => $request->get('error'),
                    'error_description' => $request->get('error_description'),
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login')->withErrors([
                    'email' => 'Azure authentication failed: ' . $request->get('error_description', 'Unknown error')
                ]);
            }
            
            // Check if authorization code is present
            if (!$request->has('code')) {
                Log::error('Azure SSO callback missing authorization code', [
                    'query_params' => $request->query(),
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login')->withErrors([
                    'email' => 'Authorization code not received from Microsoft Azure.'
                ]);
            }
            
            $azureUser = Socialite::driver('azure')->user();
            
            $azureUserId = $azureUser->getId(); // UUID dari Azure AD
            $email = $azureUser->getEmail();
            $name = $azureUser->getName();
            
            // Store Graph API token in session for later use
            $request->session()->put('azure_access_token', $azureUser->token);
            $request->session()->put('azure_refresh_token', $azureUser->refreshToken);
            $request->session()->put('azure_token_expires_at', now()->addSeconds($azureUser->expiresIn ?? 3600));
            
            // Find user by user_id (Azure UUID)
            $user = User::where('user_id', $azureUserId)->first();
            
            if (!$user) {
                // User tidak ada, buat user baru (auto-register)
                $user = new User();
                $user->user_id = $azureUserId; // Set explicitly
                $user->dealer_id = null;
                $user->name = $name ?? $email;
                $user->email = $email;
                $user->full_name = $name ?? $email;
                $user->phone = null;
                $user->is_active = '1';
                $user->created_by = $azureUserId;
                $user->updated_by = $azureUserId;
                $user->last_login = now();
                $user->save();
                
                Log::info('New user auto-registered via Azure SSO', [
                    'user_id' => $user->user_id,
                    'azure_id' => $azureUserId,
                    'email' => $user->email,
                    'name' => $user->name,
                    'ip' => $request->ip()
                ]);
            } else {
                // User sudah ada, update informasi
                $user->update([
                    'name' => $name ?? $user->name,
                    'email' => $email, // Update email jika berubah di Azure
                    'full_name' => $name ?? $user->full_name,
                    'last_login' => now(),
                    'updated_by' => $user->user_id // Update dengan user_id sendiri (Azure UUID)
                ]);
                
                Log::info('Existing user updated via Azure SSO', [
                    'user_id' => $user->user_id,
                    'azure_id' => $azureUserId,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
            }
            
            // Check if user is active
            if ($user->is_active === '0') {
                Log::warning('Azure SSO login attempt with inactive account', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.'
                ]);
            }
            
            // Login user
            Auth::login($user, true);
            
            // Regenerate session
            $request->session()->regenerate();
            
            // Log successful SSO login
            Log::info('User logged in via Azure SSO', [
                'user_id' => $user->user_id,
                'azure_id' => $azureUserId,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect()->route('dashboard');
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
            
            Log::error('Azure SSO Guzzle client error', [
                'error' => $e->getMessage(),
                'response_body' => $responseBody,
                'status_code' => $response ? $response->getStatusCode() : 'N/A',
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('login')->withErrors([
                'email' => 'Failed to authenticate with Microsoft Azure. Please check your configuration or try again later.'
            ]);
        } catch (\Exception $e) {
            Log::error('Azure SSO login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('login')->withErrors([
                'email' => 'Failed to authenticate with Microsoft Azure. Please try again.'
            ]);
        }
    }

    /**
     * Refresh Azure access token using refresh token
     */
    public function refreshAzureToken(Request $request)
    {
        try {
            if (!$request->session()->has('azure_refresh_token')) {
                return [
                    'success' => false,
                    'message' => 'No refresh token available. Please login again.'
                ];
            }

            $refreshToken = $request->session()->get('azure_refresh_token');

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://login.microsoftonline.com/' . config('services.azure.tenant') . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => config('services.azure.client_id'),
                    'client_secret' => config('services.azure.client_secret'),
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                    'scope' => 'openid profile email User.Read User.Read.All offline_access',
                ]
            ]);

            $tokenData = json_decode($response->getBody()->getContents(), true);

            // Update session with new tokens
            $request->session()->put('azure_access_token', $tokenData['access_token']);
            $request->session()->put('azure_token_expires_at', now()->addSeconds($tokenData['expires_in'] ?? 3600));

            // Update refresh token if a new one is provided
            if (isset($tokenData['refresh_token'])) {
                $request->session()->put('azure_refresh_token', $tokenData['refresh_token']);
            }

            Log::info('Azure access token refreshed successfully', [
                'user_id' => Auth::id(),
                'expires_in' => $tokenData['expires_in'] ?? 3600
            ]);

            return [
                'success' => true,
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'] ?? 3600
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';

            Log::error('Failed to refresh Azure token - Client error', [
                'error' => $e->getMessage(),
                'response_body' => $responseBody,
                'status_code' => $response ? $response->getStatusCode() : 'N/A',
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to refresh token. Please login again.'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to refresh Azure token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while refreshing token. Please login again.'
            ];
        }
    }

    /**
     * Get valid Azure access token (refresh if expired)
     *
     * @param Request $request
     * @return array ['success' => bool, 'token' => string|null, 'message' => string]
     */
    public static function getValidAzureToken(Request $request)
    {
        // Check if token exists
        if (!$request->session()->has('azure_access_token')) {
            return [
                'success' => false,
                'token' => null,
                'message' => 'No Azure access token found. Please login with Microsoft SSO first.'
            ];
        }

        $accessToken = $request->session()->get('azure_access_token');

        // Check if token is expired
        if ($request->session()->has('azure_token_expires_at')) {
            $expiresAt = $request->session()->get('azure_token_expires_at');

            // If token is expired or will expire in next 5 minutes, refresh it
            if (now()->addMinutes(5)->greaterThan($expiresAt)) {
                Log::info('Azure access token expired or expiring soon, attempting to refresh...');

                $authController = new self();
                $refreshResult = $authController->refreshAzureToken($request);

                if (!$refreshResult['success']) {
                    return [
                        'success' => false,
                        'token' => null,
                        'message' => $refreshResult['message']
                    ];
                }

                $accessToken = $refreshResult['access_token'];
                Log::info('Azure access token refreshed successfully');
            }
        }

        return [
            'success' => true,
            'token' => $accessToken,
            'message' => 'Token is valid'
        ];
    }
}