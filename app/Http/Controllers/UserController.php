<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        // Get all user IDs that have SUPERADMIN role
        $superAdminUserIds = \DB::table('ms_user_roles')
            ->join('ms_role', 'ms_user_roles.role_id', '=', 'ms_role.role_id')
            ->where('ms_role.role_code', 'SUPERADMIN')
            ->where('ms_user_roles.is_active', '1')
            ->where('ms_role.is_active', '1')
            ->pluck('ms_user_roles.user_id')
            ->toArray();
        
        $query = User::with(['roles', 'brands', 'dealer'])
            ->where('is_active', '1')
            ->whereNotIn('user_id', $superAdminUserIds); // Exclude SUPER ADMIN users
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search . '%')
                  ->orWhere('email', 'like', $search . '%')
                  ->orWhere('full_name', 'like', $search . '%')
                  ->orWhere('phone', 'like', $search . '%');
            });
        }
        
        $users = $query->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $roles = Role::where('is_active', '1')
            ->where('role_code', '!=', 'SUPERADMIN') // Hide SUPER ADMIN role
            ->get();
        $brands = \App\Models\Brand::where('is_active', '1')->orderBy('brand_name')->get();
        $dealers = \App\Models\Dealer::where('is_active', '1')->orderBy('dealer_name')->get();
        return view('users.create', compact('roles', 'brands', 'dealers'));
    }

    public function store(UserRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return redirect()->route('users.index')
            ->with('error', 'User creation is disabled. Users are managed through Azure AD SSO. Please use the Sync from Azure feature.');
    }

    public function edit(User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent editing SUPER ADMIN user
        if ($user->hasRole('SUPERADMIN')) {
            abort(403, 'SUPER ADMIN user cannot be edited.');
        }
        
        $user->load(['roles', 'brands']);
        $roles = Role::where('is_active', '1')
            ->where('role_code', '!=', 'SUPERADMIN') // Hide SUPER ADMIN role
            ->get();
        $brands = \App\Models\Brand::where('is_active', '1')->orderBy('brand_name')->get();
        $dealers = \App\Models\Dealer::where('is_active', '1')->orderBy('dealer_name')->get();
        $userRoles = $user->roles->pluck('role_id')->toArray();
        $userBrands = $user->brands->pluck('brand_id')->toArray();
        return view('users.edit', compact('user', 'roles', 'brands', 'dealers', 'userRoles', 'userBrands'));
    }

    public function update(UserRequest $request, User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Update only dealer assignment
            $user->dealer_id = $data['dealer_id'] ?? null;
            $user->updated_by = $userId;
            $user->save();

            // Soft delete existing roles (set is_active = '0')
            \DB::table('ms_user_roles')
                ->where('user_id', $user->user_id)
                ->where('is_active', '1')
                ->update([
                    'is_active' => '0',
                    'updated_by' => $userId,
                    'updated_date' => now()
                ]);

            // Add new roles
            if ($request->has('roles')) {
                foreach ($request->roles as $roleId) {
                    try {
                        $roleResult = \DB::select('CALL sp_add_ms_user_role(?, ?, ?, ?)', [
                            $user->user_id,
                            $roleId,
                            $userId,
                            (string) Str::uuid(),
                        ]);
                        
                        // Check if role assignment failed (duplicate will return 409, which is ok to ignore)
                        if (!empty($roleResult) && $roleResult[0]->return_code != 200 && $roleResult[0]->return_code != 409) {
                            \Log::warning("Failed to assign role {$roleId} to user {$user->user_id}: " . $roleResult[0]->return_message);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error assigning role {$roleId} to user {$user->user_id}: " . $e->getMessage());
                    }
                }
            }

            // Soft delete existing brands (set is_active = '0')
            \DB::table('ms_user_brand')
                ->where('user_id', $user->user_id)
                ->where('is_active', '1')
                ->update([
                    'is_active' => '0',
                    'updated_by' => $userId,
                    'updated_date' => now()
                ]);

            // Add new brands
            if ($request->has('brands')) {
                foreach ($request->brands as $brandId) {
                    try {
                        $brandResult = \DB::select('CALL sp_add_ms_user_brand(?, ?, ?, ?)', [
                            $user->user_id,
                            $brandId,
                            $userId,
                            (string) Str::uuid(),
                        ]);
                        
                        // Check if brand assignment failed (duplicate will return 409, which is ok to ignore)
                        if (!empty($brandResult) && $brandResult[0]->return_code != 200 && $brandResult[0]->return_code != 409) {
                            \Log::warning("Failed to assign brand {$brandId} to user {$user->user_id}: " . $brandResult[0]->return_message);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error assigning brand {$brandId} to user {$user->user_id}: " . $e->getMessage());
                    }
                }
            }

            // Flush cache
            Cache::flush();

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in UserController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in UserController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(User $user)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('users.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if user has Super Admin role
        if ($user->hasRole('SUPERADMIN')) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user with Super Admin role.');
        }
        
        // Check if user has Admin role
        if ($user->hasRole('ADMIN')) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user with Admin role.');
        }
        
        // Prevent deleting yourself
        if ($user->user_id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }
        
        $user->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        
        // Flush cache
        Cache::flush();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Sync users from Microsoft Azure Graph API
     */
    
        
            public function syncFromAzure(Request $request)
            {
                try {
                    // Get valid Azure access token (will auto-refresh if expired)
                    $tokenResult = \App\Http\Controllers\AuthController::getValidAzureToken($request);

                    if (!$tokenResult['success']) {
                        return response()->json([
                            'success' => false,
                            'message' => $tokenResult['message']
                        ], 401);
                    }

                    $accessToken = $tokenResult['token'];

                    // Fetch users from Microsoft Graph API
                    $graphUsers = $this->fetchUsersFromGraph($accessToken);

                    if (!$graphUsers) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to fetch users from Microsoft Graph API.'
                        ], 500);
                    }

                    $syncedCount = 0;
                    $updatedCount = 0;
                    $createdCount = 0;
                    $errors = [];

                    foreach ($graphUsers as $graphUser) {
                        try {
                            $azureUserId = $graphUser['id'];
                            $email = $graphUser['mail'] ?? $graphUser['userPrincipalName'];
                            $displayName = $graphUser['displayName'];
                            $givenName = $graphUser['givenName'] ?? '';
                            $surname = $graphUser['surname'] ?? '';
                            $mobilePhone = $graphUser['mobilePhone'] ?? null;

                            // Find user by Azure user_id
                            $user = User::where('user_id', $azureUserId)->first();

                            if ($user) {
                                // Update existing user
                                $user->update([
                                    'name' => $displayName,
                                    'email' => $email,
                                    'full_name' => trim($givenName . ' ' . $surname) ?: $displayName,
                                    'phone' => $mobilePhone,
                                    'updated_by' => auth()->id(),
                                ]);
                                $updatedCount++;
                            } else {
                                // Create new user
                                $user = new User();
                                $user->user_id = $azureUserId;
                                $user->dealer_id = null;
                                $user->name = $displayName;
                                $user->email = $email;
                                $user->full_name = trim($givenName . ' ' . $surname) ?: $displayName;
                                $user->phone = $mobilePhone;
                                $user->is_active = '1';
                                $user->created_by = auth()->id();
                                $user->updated_by = auth()->id();
                                $user->save();
                                $createdCount++;
                            }

                            $syncedCount++;

                        } catch (\Exception $e) {
                            \Log::error('Error syncing user from Azure: ' . $e->getMessage(), [
                                'azure_user_id' => $azureUserId ?? 'unknown',
                                'email' => $email ?? 'unknown'
                            ]);
                            $errors[] = [
                                'email' => $email ?? 'unknown',
                                'error' => $e->getMessage()
                            ];
                        }
                    }

                    // Flush cache
                    Cache::flush();

                    return response()->json([
                        'success' => true,
                        'message' => "Successfully synced {$syncedCount} users from Azure AD.",
                        'data' => [
                            'total_synced' => $syncedCount,
                            'created' => $createdCount,
                            'updated' => $updatedCount,
                            'errors' => $errors
                        ]
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Error in syncFromAzure: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'An error occurred while syncing users: ' . $e->getMessage()
                    ], 500);
                }
            }



    /**
     * Fetch users from Microsoft Graph API
     * 
     * @param string $accessToken
     * @return array|null
     */
    private function fetchUsersFromGraph($accessToken)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://graph.microsoft.com/v1.0/users', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    '$select' => 'id,displayName,givenName,surname,mail,userPrincipalName,mobilePhone',
                    '$top' => 999 // Maximum users per request
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['value'] ?? null;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('Graph API Client Error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                \Log::error('Response: ' . $e->getResponse()->getBody()->getContents());
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('Error fetching users from Graph API: ' . $e->getMessage());
            return null;
        }
    }
}
