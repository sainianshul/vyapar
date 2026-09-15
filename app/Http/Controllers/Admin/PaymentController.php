<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Payments\WalletTransactionDataTable;
use App\DataTables\Payments\WithdrawalRequestDataTable;
use App\DataTables\Payments\PaymentLogDataTable;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Models\PaymentLog;

class PaymentController extends Controller
{
    /**
     * Display all wallet transactions (platform money movement).
     */
    public function transactions(WalletTransactionDataTable $dataTable)
    {
        return $dataTable->render('admin.payments.transactions', [
            'types' => WalletTransaction::getTypeList(),
            'reasons' => WalletTransaction::getReasonList(),
        ]);
    }

    /**
     * Get transactions data (AJAX)
     */
    public function transactionsData(WalletTransactionDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    /**
     * Display all nurse payouts (withdrawal requests).
     */
    public function payouts(WithdrawalRequestDataTable $dataTable)
    {
        return $dataTable->render('admin.payments.payouts', [
            'statuses' => WithdrawalRequest::getStatusList(),
        ]);
    }

    /**
     * Get payouts data (AJAX)
     */
    public function payoutsData(WithdrawalRequestDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    /**
     * Display all refunds (payment logs filtered by refund events).
     */
    public function refunds(PaymentLogDataTable $dataTable)
    {
        return $dataTable->render('admin.payments.refunds');
    }

    /**
     * Get refunds data (AJAX)
     */
    public function refundsData(PaymentLogDataTable $dataTable)
    {
        request()->merge(['only_refunds' => '1']);
        return $dataTable->ajax();
    }
}
