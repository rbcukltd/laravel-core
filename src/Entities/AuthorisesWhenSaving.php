<?php
namespace Dapatchi\LaravelCore\Entities;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

trait AuthorisesWhenSaving
{
    public static function bootAuthorisesWhenSaving()
    {
        static::creating(function ($entity) {
            $loggedInUser = Auth::user();

            if (!$loggedInUser || !$loggedInUser->can('create', $entity)) {
                throw new AuthorizationException();
            }
        });

        static::updating(function ($entity) {
            $loggedInUser = Auth::user();

            if (!$loggedInUser || !$loggedInUser->can('update', $entity)) {
                throw new AuthorizationException();
            }
        });

        static::deleting(function ($entity) {
            $loggedInUser = Auth::user();

            if (!$loggedInUser || !$loggedInUser->can('delete', $entity)) {
                throw new AuthorizationException();
            }
        });
    }
}
