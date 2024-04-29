<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AccountController;

class TransactionController extends Controller
{
    private AccountController $accountController;
    const CREDIT = "C";
    const DEBIT = "D";
    public function __construct(
        AccountController $accountController
    ) {
        $this->accountController = $accountController;
    }
    public function store(Request $request)
    {

        $user = auth()->user();

        // Start a database transaction
        DB::beginTransaction();

        try {

            // Validation rules
            $validator = Validator::make($request->all(), [
                'conta_id' => 'required|exists:users,conta_id',
                'valor' => 'required|numeric|min:0',
                'forma_pagamento' => 'required|in:D,C,P',
            ]);
            if(empty($user)){
                DB::rollBack();
                return response()->json(['error' => 'User not fount. Please login again'], 422);
            }

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['error' => $validator->errors()], 422);
            }

            if($request->conta_id == $user->conta_id){
                DB::rollBack();
                return response()->json(['error' => 'Cannot send by the same user'], 422);
            }


            // If validation fails, rollback the transaction and return the errors


            $value = $request->valor;
            if($request->forma_pagamento === self::DEBIT){
                if((float)$user->saldo <= $value * 1.05){
                    DB::rollBack();
                    return response()->json(['error' => 'Unavailable balance'], 404);
                }
                else{
                    $valueRemoved = $user->saldo - $value * 1.05;
                    $this->accountController->update($user->conta_id,$valueRemoved);
                    $accountRecieve =  $this->accountController->getById($request->conta_id);
                    $valueAdded = $accountRecieve->saldo + $value;
                    $this->accountController->update($request->conta_id, $valueAdded);
                }
            }
            else if($request->forma_pagamento === self::CREDIT){
                if((float)$user->saldo <= $value * 1.03){
                    DB::rollBack();
                    return response()->json(['error' => 'Unavailable balance'], 404);
                }
                else{
                    $valueRemoved = $user->saldo - $value * 1.03;
                    $this->accountController->update($user->conta_id,$valueRemoved);
                    $accountRecieve =  $this->accountController->getById($request->conta_id);
                    $valueAdded = $accountRecieve->saldo + $value;
                    $this->accountController->update($request->conta_id, $valueAdded);
                }
            }
            else{
                if((float)$user->saldo <= $value){
                    DB::rollBack();
                    return response()->json(['error' => 'Unavailable balance'], 404);
                }
                else{
                    $valueRemoved = $user->saldo - $value;
                    $this->accountController->update($user->conta_id,$valueRemoved);
                    $accountRecieve =  $this->accountController->getById($request->conta_id);
                    $valueAdded = $accountRecieve->saldo + $value;
                    $this->accountController->update($request->conta_id, $valueAdded);
                }
            }

            $transacao = Transaction::create([
                'account_origin' => $user->conta_id,
                'account_destination' => $request->conta_id,
                'transaction_value' => $request->valor,
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Transaction created successfully', 'transacao' => $transacao], 201);
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction and return an error response
            DB::rollBack();
            return response()->json(['error' => 'Failed to create transaction'], 500);
        }
    }
}
