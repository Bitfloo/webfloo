<?php

declare(strict_types=1);

namespace Webfloo\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Webfloo\Models\Lead;

class LeadWebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $this->validateWebhookSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'source_name' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Check for duplicate by external_id
        if (! empty($data['external_id'])) {
            $existing = Lead::where('external_id', $data['external_id'])->first();
            if ($existing) {
                return response()->json([
                    'error' => 'Lead with this external_id already exists',
                    'lead_id' => $existing->id,
                ], 409);
            }
        }

        // Check for duplicate by email (within last 24 hours to allow re-submissions)
        $recentDuplicate = Lead::where('email', $data['email'])
            ->where('source', Lead::SOURCE_WEBHOOK)
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if ($recentDuplicate) {
            return response()->json([
                'error' => 'Lead with this email was recently created',
                'lead_id' => $recentDuplicate->id,
            ], 409);
        }

        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => Lead::SOURCE_WEBHOOK,
            'status' => Lead::STATUS_NEW,
            'external_id' => $data['external_id'] ?? null,
            'estimated_value' => $data['estimated_value'] ?? null,
            'currency' => 'PLN',
        ]);

        // Log the webhook activity with metadata
        /** @var array<string, mixed> $metadata */
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $sourceName = $data['source_name'] ?? 'unknown';
        $metadata['source_name'] = is_string($sourceName) ? $sourceName : 'unknown';

        $lead->activities()->create([
            'type' => 'webhook',
            'title' => 'Lead utworzony przez webhook',
            'description' => $data['source_name'] ?? 'External source',
            'metadata' => $metadata,
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'message' => 'Lead created successfully',
        ], 201);
    }

    public function update(Request $request, string $externalId): JsonResponse
    {
        if (! $this->validateWebhookSecret($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lead = Lead::where('external_id', $externalId)->first();

        if (! $lead) {
            return response()->json(['error' => 'Lead not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string|in:new,contacted,qualified,converted,lost',
            'note' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Update status if provided
        $status = $data['status'] ?? null;
        if (is_string($status) && $status !== '' && $status !== $lead->status) {
            $lead->transitionTo($status);
        }

        // Add note if provided
        $note = $data['note'] ?? null;
        if (is_string($note) && $note !== '') {
            $lead->addNote($note);
        }

        // Update estimated value if provided
        if (isset($data['estimated_value'])) {
            $lead->update(['estimated_value' => $data['estimated_value']]);
        }

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'message' => 'Lead updated successfully',
        ]);
    }

    protected function validateWebhookSecret(Request $request): bool
    {
        $secret = config('webfloo.webhook_secret');

        if (empty($secret) || ! is_string($secret)) {
            return false;
        }

        return hash_equals($secret, (string) $request->header('X-Webhook-Secret', ''));
    }
}
