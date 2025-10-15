<?php

namespace App\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Services\Auth\JWTService;
use App\Helpers\Response;

/**
 * Subscription Controller
 * Handles subscription management, upgrades, and cancellations
 */
class SubscriptionController
{
    private Subscription $subscriptionModel;
    private SubscriptionPlan $planModel;
    private Payment $paymentModel;
    private JWTService $jwtService;

    public function __construct()
    {
        $this->subscriptionModel = new Subscription();
        $this->planModel = new SubscriptionPlan();
        $this->paymentModel = new Payment();
        $this->jwtService = new JWTService();
    }

    /**
     * Get current user's subscription
     * GET /api/subscriptions/current
     */
    public function current(array $params = []): void
    {
        // Get authenticated user
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user) {
            Response::unauthorized('User not authenticated');
        }

        // Super admin bypass
        if ($user['is_super_admin']) {
            Response::success([
                'is_super_admin' => true,
                'message' => 'Super admin - no subscription required'
            ]);
        }

        if (!$user['company_id']) {
            Response::error('User has no company', 400);
        }

        // Get subscription
        $subscription = $this->subscriptionModel->getByCompanyId($user['company_id']);

        if (!$subscription) {
            Response::notFound('No subscription found');
        }

        // Check if active
        $isActive = $this->subscriptionModel->isActive($user['company_id']);
        $isInTrial = $this->subscriptionModel->isInTrial($user['company_id']);
        $daysRemaining = $this->subscriptionModel->getDaysRemaining($user['company_id']);

        Response::success([
            'subscription' => $subscription,
            'is_active' => $isActive,
            'is_in_trial' => $isInTrial,
            'days_remaining' => $daysRemaining
        ]);
    }

    /**
     * Get all available subscription plans
     * GET /api/subscriptions/plans
     */
    public function plans(array $params = []): void
    {
        $plans = $this->planModel->all();

        Response::success([
            'plans' => $plans
        ]);
    }

    /**
     * Upgrade subscription plan
     * POST /api/subscriptions/upgrade
     */
    public function upgrade(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user) {
            Response::unauthorized('User not authenticated');
        }

        if (!$user['company_id']) {
            Response::error('User has no company', 400);
        }

        $input = $this->getJsonInput();

        // Validation
        if (empty($input['plan_type'])) {
            Response::validationError(['plan_type' => 'Plan type is required']);
        }

        if (empty($input['months'])) {
            $input['months'] = 1; // Default 1 month
        }

        // Verify plan exists
        $plan = $this->planModel->findBy('slug', $input['plan_type']);
        
        if (!$plan) {
            Response::notFound('Plan not found');
        }

        if ($plan['slug'] === 'trial') {
            Response::error('Cannot upgrade to trial plan', 400);
        }

        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getByCompanyId($user['company_id']);

        if (!$currentSubscription) {
            Response::error('No active subscription found', 400);
        }

        // Calculate amount
        $amount = $plan['price_monthly'] * $input['months'];

        try {
            // Start transaction
            $this->subscriptionModel->beginTransaction();

            // Create payment record
            $paymentId = $this->paymentModel->create([
                'company_id' => $user['company_id'],
                'subscription_id' => $currentSubscription['id'],
                'amount' => $amount,
                'currency' => 'TRY',
                'status' => 'pending',
                'payment_method' => $input['payment_method'] ?? 'credit_card',
                'payment_date' => date('Y-m-d H:i:s')
            ]);

            if (!$paymentId) {
                $this->subscriptionModel->rollback();
                Response::serverError('Failed to create payment record');
            }

            // TODO: Integrate with payment gateway (Stripe/Iyzico)
            // For now, mark as completed
            $this->paymentModel->update($paymentId, [
                'status' => 'completed',
                'transaction_id' => 'DEMO_' . time()
            ]);

            // Upgrade subscription
            $success = $this->subscriptionModel->upgradePlan(
                $user['company_id'],
                $input['plan_type'],
                $input['months']
            );

            if (!$success) {
                $this->subscriptionModel->rollback();
                Response::serverError('Failed to upgrade subscription');
            }

            // Commit transaction
            $this->subscriptionModel->commit();

            // Get updated subscription
            $newSubscription = $this->subscriptionModel->getByCompanyId($user['company_id']);

            Response::success([
                'subscription' => $newSubscription,
                'payment_id' => $paymentId,
                'message' => 'Subscription upgraded successfully'
            ], 'Subscription upgraded');

        } catch (\Exception $e) {
            $this->subscriptionModel->rollback();
            logger('Subscription upgrade failed: ' . $e->getMessage(), 'error');
            Response::serverError('Upgrade failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     * POST /api/subscriptions/cancel
     */
    public function cancel(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user) {
            Response::unauthorized('User not authenticated');
        }

        if (!$user['company_id']) {
            Response::error('User has no company', 400);
        }

        $input = $this->getJsonInput();

        // Get current subscription
        $subscription = $this->subscriptionModel->getByCompanyId($user['company_id']);

        if (!$subscription) {
            Response::notFound('No subscription found');
        }

        if ($subscription['status'] === 'cancelled') {
            Response::error('Subscription already cancelled', 400);
        }

        // Cancel subscription (will remain active until period end)
        $success = $this->subscriptionModel->cancelSubscription($user['company_id'], $input['reason'] ?? null);

        if (!$success) {
            Response::serverError('Failed to cancel subscription');
        }

        // Get updated subscription
        $updatedSubscription = $this->subscriptionModel->getByCompanyId($user['company_id']);

        Response::success([
            'subscription' => $updatedSubscription,
            'message' => 'Subscription cancelled. Access will continue until ' . $subscription['current_period_end']
        ], 'Subscription cancelled');
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?? [];
    }
}
