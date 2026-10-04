<?php

// namespace App\Scopes;

// use Illuminate\Database\Eloquent\Scope;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\Auth;

// class LimitedOrderScope implements Scope
// {
//     /**
//      * Apply the scope to a given Eloquent query builder.
//      * Only apply when an authenticated user exists and matches the
//      * limited-user condition: email or is_limited_user flag.
//      */
//     public function apply(Builder $builder, Model $model)
//     {
//         if (!Auth::check()) {
//             return; // skip for unauthenticated or console contexts
//         }

//         $user = Auth::user();

//         if (!$user) {
//             return;
//         }

//         $isLimitedByEmail = isset($user->email) && strtolower($user->email) === 'admin-second@email.com';
//         $isLimitedFlag = isset($user->is_limited_user) && (bool) $user->is_limited_user;

//         if ($isLimitedByEmail || $isLimitedFlag) {
//             // Use a deterministic filter that selects roughly half the rows. 50% of orders will be included.
//             // Avoid random() and non-deterministic functions.
//             $builder->whereRaw('(orders.id % 2) = 0');
//         }

//         $isLimitedByEmailTwo = isset($user->email) && strtolower($user->email) === 'admin-third@email.com';
//         $isLimitedFlagTwo = isset($user->is_limited_user) && (bool) $user->is_limited_user;

//         if ($isLimitedByEmailTwo || $isLimitedFlagTwo) {
//             // Use a deterministic filter that selects roughly half the rows. 40% of orders will be included.
//             // Avoid random() and non-deterministic functions.
//             $builder->whereRaw('(orders.id % 5) IN (0,1)');
//         }
//         $isLimitedByEmailThree = isset($user->email) && strtolower($user->email) === 'admin-fourth@email.com';
//         $isLimitedFlagThree = isset($user->is_limited_user) && (bool) $user->is_limited_user;

//         if ($isLimitedByEmailThree || $isLimitedFlagThree) {
//             // Use a deterministic filter that selects roughly half the rows. 80% of orders will be included.
//             // Avoid random() and non-deterministic functions.
//             $builder->whereRaw('(orders.id % 5 != 0)');
//         }
//     }
// }



namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LimitedOrderScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Skip if user not logged in
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Safety check
        if (!$user) {
            return;
        }

        // Current logged-in email
        $email = strtolower($user->email ?? '');

        /*
        |--------------------------------------------------------------------------
        | Order Visibility Rules
        |--------------------------------------------------------------------------
        |
        | admin-second  => 50%
        | admin-third   => 40%
        | admin-fourth  => 80%
        |
        */

        $rules = [

            // 50% Orders
            'admin-second@gmail.com' => '(orders.id % 2) = 0',

            // 40% Orders
            'admin-third@gmail.com'  => '(orders.id % 5) IN (0,1)',

            // 80% Orders
            'admin-fourth@gmail.com' => '(orders.id % 5) != 0',

        ];

        // Apply matching rule
        if (isset($rules[$email])) {

            $builder->whereRaw($rules[$email]);

        }
    }
}



