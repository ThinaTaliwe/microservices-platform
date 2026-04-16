<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShipmentWebController extends Controller
{
    public function create()
    {
        return view('bfrn.shipcreate');
    }

    public function store(Request $request)
    {
        $isAjax = $request->expectsJson() || $request->ajax() || $request->wantsJson();
        $requestId = (string) ($request->headers->get('X-Request-Id') ?: Str::uuid());

        $validator = Validator::make($request->all(), [
            // Shipment payload
            'name' => ['required', 'string', 'max:255'],
            'bu' => ['required', 'integer'],
            'shipment_type' => ['required', 'integer'],
            'mode_of_transport' => ['required', 'integer'],
            'shipment_instruction' => ['required', 'integer'],

            // Instruction payload (required for troubleshooting/data consistency)
            'instruction_type' => ['required', 'integer'],
            'instruction_reference' => ['required', 'string', 'max:255'],
            'from_address' => ['required', 'integer'],
            'to_address' => ['required', 'integer'],

            // Optional fields
            'description' => ['nullable', 'string'],
            'instruction_detail' => ['nullable', 'string'],
            'loading' => ['nullable', 'integer'],
            'movement' => ['nullable', 'integer'],
            'offloading' => ['nullable', 'integer'],
            'storage' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                    'request_id' => $requestId,
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator)
                ->with('error', 'Please complete all required fields before submitting.');
        }

        $base = rtrim(config('services.siya.base_url'), '/');
        $prefix = trim(config('services.siya.prefix', ''), '/');
        $token = config('services.siya.token');

        $baseUrl = $prefix ? "{$base}/{$prefix}" : $base;
        $endpointPath = '/api/shipments/shipments/';
        $endpoint = rtrim($baseUrl, '/') . $endpointPath;

        $payload = $validator->validated();

        try {
            $client = Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-Request-Id' => $requestId,
                ]);

            if ($token) {
                $client = $client->withToken($token);
            }

            $response = $client->post($endpoint, $payload);

            Log::info('Siya shipment request completed.', [
                'request_id' => $requestId,
                'endpoint' => $endpoint,
                'response_status' => $response->status(),
            ]);

            $responseData = $response->json();

            if (!$response->successful()) {
                $normalizedErrors = $this->normalizeSiyaErrors($responseData);

                if ($isAjax) {
                    return response()->json([
                        'message' => 'Failed to create shipment in Siya.',
                        'errors' => $normalizedErrors,
                        'request_id' => $requestId,
                    ], $response->status());
                }

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Failed to create shipment in Siya.')
                    ->with('siya_errors', $normalizedErrors);
            }

            if ($isAjax) {
                return response()->json([
                    'message' => 'Shipment created successfully.',
                    'data' => $responseData,
                    'request_id' => $requestId,
                ], $response->status());
            }

            return redirect()
                ->route('bfrn.shipments.create')
                ->with('success', 'Shipment created successfully.')
                ->with('shipment', $responseData)
                ->with('request_id', $requestId);
        } catch (\Throwable $e) {
            Log::error('Siya shipment request failed.', [
                'request_id' => $requestId,
                'endpoint' => $endpoint,
                'response_status' => null,
                'exception' => $e->getMessage(),
            ]);

            if ($isAjax) {
                return response()->json([
                    'message' => 'Unable to reach Siya service.',
                    'errors' => ['service' => ['Please try again shortly.']],
                    'request_id' => $requestId,
                ], 502);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unable to reach Siya service. Please try again shortly.');
        }
    }

    private function normalizeSiyaErrors($payload): array
    {
        if (!is_array($payload)) {
            return ['Siya returned an unexpected error response.'];
        }

        $messages = [];

        $flatten = function ($value) use (&$flatten, &$messages) {
            if (is_string($value)) {
                $messages[] = $value;
                return;
            }

            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    if (is_string($item)) {
                        $label = is_string($key) ? str_replace('_', ' ', $key) : 'Error';
                        $messages[] = ucfirst($label) . ': ' . $item;
                        continue;
                    }

                    $flatten($item);
                }
            }
        };

        if (isset($payload['detail']) && is_string($payload['detail'])) {
            $messages[] = $payload['detail'];
        }

        $flatten($payload);

        $messages = array_values(array_unique(array_filter($messages)));

        return $messages ?: ['Unable to process request in Siya.'];
    }
}
