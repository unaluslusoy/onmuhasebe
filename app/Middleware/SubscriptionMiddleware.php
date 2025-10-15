<?php

namespace App\Middleware;

use App\Models\Subscription;
use App\Helpers\Response;

/**
 * Subscription Middleware
 * Checks if user's company has an active subscription
 * Super admins are exempt from subscription checks
 */
class SubscriptionMiddleware
{
    private Subscription $subscriptionModel;

    public function __construct()
    {
        $this->subscriptionModel = new Subscription();
    }

    /**
     * Handle the request
     * 
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        // Get authenticated user from request (set by AuthMiddleware)
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user) {
            Response::unauthorized('Authentication required');
        }

        // Super admins bypass subscription checks
        if (isset($user['is_super_admin']) && $user['is_super_admin'] == 1) {
            return $next();
        }

        // Check if user has a company
        if (empty($user['company_id'])) {
            Response::forbidden('No company associated with this account');
        }

        // Check if subscription is active
        if (!$this->subscriptionModel->isActive($user['company_id'])) {
            $subscription = $this->subscriptionModel->getByCompanyId($user['company_id']);
            
            $message = 'Subscription expired or inactive';
            $data = [
                'subscription_status' => $subscription['status'] ?? 'none',
                'action_required' => 'upgrade'
            ];

            // If was in trial
            if ($subscription && $subscription['status'] === 'expired' && $subscription['trial_ends_at']) {
                $message = 'Your 30-day trial has expired. Please upgrade to continue.';
                $data['trial_ended'] = true;
            }

            Response::paymentRequired($message, $data);
        }

        // Add subscription info to request
        $subscription = $this->subscriptionModel->getByCompanyId($user['company_id']);
        $_REQUEST['subscription'] = $subscription;
        $_REQUEST['days_remaining'] = $this->subscriptionModel->getDaysRemaining($user['company_id']);

        // Continue to next middleware or controller
        return $next();
    }
}
