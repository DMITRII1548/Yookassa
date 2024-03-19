<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatusEnum;
use App\Models\Transaction;
use App\Services\PaymentService;
use Faker\Provider\Lorem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;

class PaymentController extends Controller
{
    // Get payment
    public function index()
    {
        $transactions = Transaction::all();
        return view('payments.index', compact('transactions'));
    }

    public function create(Request $request, PaymentService $service)
    {
        $amount = (float)$request->input('amount');
        $description = (string)$request->input('description');

        $transaction = Transaction::create([
            'amount' => $amount,
            'description' => $description,
        ]);

        if ($transaction) {
            $link = $service->createPayment($amount, $description, [
                'transaction_id' => $transaction->id
            ]);

            return redirect()->away($link);
        }
    }

    // Get payment status
    public function callback(Request $request, PaymentService $service)
    {
        $source = file_get_contents('php://input');
        // Log::error($source);
        $requestBody = json_decode($source, true);

        $notification = (isset($requestBody['event']) && $requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
            ? new NotificationSucceeded($requestBody)
            : new NotificationWaitingForCapture($requestBody);

        $payment = $notification->getObject();
        Log::error(json_encode($payment));
        if (isset($payment->status) && $payment->status === 'waiting_for_capture') {
            $service->getClient()->capturePayment([
                'amount' => $payment->amount
            ], $payment->id, uniqid('', true));
        }

        if (isset($payment->status) && $payment->status === 'succeeded') {
            if ((bool)$payment->paid === true) {
                $metadata = (object)$payment->metadata;

                if (isset($metadata->transaction_id)) {
                    $transactionId = (int)$metadata->transaction_id;
                    $transaction = Transaction::findOrFail($transactionId);
                    $transaction->status = PaymentStatusEnum::CONFIRMED;
                    $transaction->save();

                    if (Cache::has('amount')) {
                        Cache::forever('balance',
                            (float)Cache::get('balance') + (float)$payment->amount->value
                        );
                    } else {
                        Cache::forever('balance', $payment->amount->value);
                    }
                }
            }
        }
    }
}
