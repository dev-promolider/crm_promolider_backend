<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{

    public function index()
    {
    }

    public function createTransaction($requestData) // Changed from $request to avoid confusion with Request object
    {
        try {
            // 1. Validate the incoming data first
            $validator = Validator::make($requestData, [
                'id' => 'required',
                'authorization' => 'required|string',
                'operation_type' => 'required|string',
                'transaction_type' => 'required|string',
                'status' => 'required|string',
                'conciliated' => 'required|boolean',
                'creation_date' => 'required|date',
                'operation_date' => 'required|date',
                'description' => 'required|string',
                'error_message' => 'nullable|string',
                'order_id' => 'required|string',
                'card' => 'required|array',
                'due_date' => 'nullable|date', // Use 'nullable' if it's optional, 'required' if it's mandatory
                'amount' => 'required|numeric',
                'customer' => 'required|array',
                'fee' => 'nullable|array',
                'payment_method' => 'required|array',
                'metadata' => 'nullable|array',
                'currency' => 'required|string|size:3',
                'method' => 'required|string',
            ]);

            if ($validator->fails()) {
                // This will throw an exception that Laravel's handler will catch,
                // and it will return a proper 422 JSON response with the errors.
                throw new ValidationException($validator);
            }

            // 2. Use the validated and safe data
            $dataServer = $validator->validated();

            $transaction = new Transaction();

            $transaction->operation_number = $dataServer['id'];
            $transaction->authorization = $dataServer['authorization'];
            $transaction->operation_type = $dataServer['operation_type'];
            $transaction->transaction_type = $dataServer['transaction_type'];
            $transaction->status = $dataServer['status'];
            $transaction->conciliated = $dataServer['conciliated'];

            // No need to parse dates if you use the 'date' validation rule,
            // as Laravel can cast them automatically. But parsing is still fine.
            $transaction->creation_date = \Carbon\Carbon::parse($dataServer['creation_date']);
            $transaction->operation_date = \Carbon\Carbon::parse($dataServer['operation_date']);
            
            // Use the defensive check from Solution 1 here, which works well with validation
            $dueDateValue = $dataServer['due_date'] ?? null;
            $transaction->due_date = $dueDateValue ? \Carbon\Carbon::parse($dueDateValue) : null;

            $transaction->description = $dataServer['description'];
            $transaction->error_message = $dataServer['error_message'];
            $transaction->order_id = $dataServer['order_id'];
            $transaction->amount = $dataServer['amount'];
            $transaction->currency = $dataServer['currency'];
            $transaction->method = $dataServer['method'];
            
            // For array fields, use the validated data directly
            $transaction->card = json_encode($dataServer['card']);
            $transaction->customer = json_encode($dataServer['customer']);
            $transaction->fee = isset($dataServer['fee']) ? json_encode($dataServer['fee']) : null;
            $transaction->payment_method = json_encode($dataServer['payment_method']);
            $transaction->metadata = isset($dataServer['metadata']) ? json_encode($dataServer['metadata']) : null;
            
            $transaction->save();
            
        } catch (ValidationException $e) {
            // Log validation errors specifically
            Log::error('Transaction validation failed', [
                'errors' => $e->errors()
            ]);
            // Re-throw or handle as needed
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al procesar la transaccion', ['error_message' => $e->getMessage()]);
        }
    }
}
