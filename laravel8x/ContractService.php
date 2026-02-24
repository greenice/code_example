<?php

namespace App\Domains\Core\Services;

use App\Domains\Auth\Models\User;
use App\Domains\Core\Constants\ContractConstant;
use App\Domains\Core\Events\StatusChanged;
use App\Domains\Core\Models\Contract\Contract;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContractService extends BaseService
{
    protected $siteUrl;
    protected $baseRequest;
    protected $escrowMyUrl;
    protected $apiUrl;

    public function __construct(Contract $model)
    {
        $this->model = $model;
        $this->apiUrl = 'https://api.' . config('services.escrow.escrowUrl') . '/2017-09-01';
        $this->escrowMyUrl = 'https://my.' . config('services.escrow.escrowUrl');
        $this->baseRequest = Http::withBasicAuth(
                config('services.escrow.email'), config('services.escrow.apiKey')
            )
            ->acceptJson();
    }

    /**
     * @param Model $model
     * @param array $data
     * @return string[]|Contract
     */
    public function create(Model $model, array $data = [])
    {
        $escrowTransaction = $this->createTransaction($data);

        if (!$escrowTransaction->successful()) {
            Log::error('contract create', ['body' => $escrowTransaction->body(), 'data' => $data]);
            return ['error' => $escrowTransaction['error'] ?? 'Validation error'];
        }

        $data = [
            'buyer_id' => $data['buyer_id'],
            'seller_id' => $data['seller_id'],
            'status' => ContractConstant::STATUS_NEW,
            'amount' => $data['amount'],
            'description' => $data['transactionDescription'],
            'transaction_id' => $escrowTransaction['id'],
            'params' => [
                'broker_fee' => $data['brokerFeeAmount'],
                'deadline_at' => $data['deadline_at'],
                'payment_type' => $data['payment_type'],
                'buyer_description' => $data['buyer_description']
            ]
        ];

        $contract = $model->contract()->create($data);

        event(new StatusChanged($contract->fresh(), null));

        return $contract;
    }

    public function update(Contract $contract, array $data = []): Contract
    {
        return DB::transaction(function () use ($data, $contract) {
            $oldStatus = $contract->status;

            $contract->update($data);

            $cacheStatus = Cache::get('contract-' . $contract->id);

            if (empty($cacheStatus) || (isset($data['status']) && $cacheStatus !== $data['status'])) {
                Cache::put('contract-' . $contract->id, $data['status'], 10);

                event(new StatusChanged($contract, $oldStatus));
            }

            return $contract;
        });
    }

    public function getContractByTransactionId($transactionId)
    {
        return Contract::where('transaction_id', $transactionId)->firstOrFail();
    }

    private function createTransaction($data)
    {
        $response = $this->baseRequest
            ->post($this->apiUrl . '/transaction', [
                'currency' => 'usd',
                'items' => [
                    [
                        'description' => Str::limit($data['productDescription']),
                        'schedule' => [
                            [
                                'payer_customer' => $data['businessEmail'],
                                'amount' => $data['amount'],
                                'beneficiary_customer' => $data['adviserEmail'],
                            ],
                        ],
                        'title' => Str::limit($data['productDescription']),
                        'inspection_period' => '604800',
                        'type' => 'general_merchandise',
                        'category' => 'services',
                        'quantity' => '1',
                        'shipping_type' => 'no_shipping',
                        'fees' => [
                            [
                                'payer_customer' => $data['businessEmail'],
                                'type' => 'escrow',
                                'split' => '0.5',
                            ],
                            [
                                'payer_customer' => $data['adviserEmail'],
                                'type' => 'escrow',
                                'split' => '0.5',
                            ],
                        ],
                    ],
                    [
                        'type' => 'broker_fee',
                        'schedule' => [
                            [
                                'payer_customer' => $data['businessEmail'],
                                'amount' => $data['brokerFeeAmount'],
                                'beneficiary_customer' => 'me',
                            ],
                        ],
                    ],
                ],
                'description' => $data['transactionDescription'],
                'parties' => [
                    [
                        'customer' => 'me',
                        'role' => 'broker',
                    ],
                    [
                        'customer' => $data['businessEmail'],
                        'role' => 'buyer',
                    ],
                    [
                        'customer' => $data['adviserEmail'],
                        'role' => 'seller',
                    ],
                ],
            ]);

        return $response;
    }

    public function getTransactionById($transactionId)
    {
        return $this->fetchTransactionById($transactionId);
    }

    private function fetchTransactionById($transactionId)
    {
        return $this->baseRequest
            ->get($this->apiUrl . '/transaction/' . $transactionId);
    }

    public function agree(Contract $contract, User $user)
    {
        return $this->agreeTransaction($contract->transaction_id, $user);
    }

    private function agreeTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'agree'
            ]);
    }

    public function funding(Contract $contract, User $user)
    {
        return $this->fundingTransaction(
                $contract->transaction_id,
                route('frontend.core.contract.view', $contract),
                $user
            );
    }

    private function fundingTransaction($transactionId, $resultUrl, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->post($this->apiUrl . '/transaction/' . $transactionId . '/payment_methods/credit_card', [
                'return_url' => $resultUrl
            ]);
    }

    public function ship(Contract $contract, User $user)
    {
        return $this->shipTransaction($contract->transaction_id, $user);
    }

    private function shipTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'ship'
            ]);
    }

    public function receive(Contract $contract, User $user)
    {
        return $this->receiveTransaction($contract->transaction_id, $user);
    }

    private function receiveTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'receive'
            ]);
    }

    public function accept(Contract $contract, User $user)
    {
        return $this->acceptTransaction($contract->transaction_id, $user);
    }

    private function acceptTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'accept'
            ]);
    }

    public function cancel(Contract $contract, User $user)
    {
        if ($user->isAdmin()) {
            $response = $this->brokerCancelTransaction($contract->transaction_id, $user);
        } else {
            $response = $this->cancelTransaction($contract->transaction_id, $user);
        }

        if (!empty($response['is_cancelled']) && $response['is_cancelled'] === true) {
            $contract->update(['status' => ContractConstant::STATUS_CANCELED]);
        }

        return $response;
    }

    private function cancelTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->withHeaders([
                'As-Customer' => $user->email
            ])
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'cancel',
                'cancel_information' => [
                    'cancellation_reason' => 'Transaction canceled by ' . $user->email
                ]
            ]);
    }

    /**
     * If both the buyer and seller have agreed to the transaction then only the listed partner can cancel
     */
    private function brokerCancelTransaction($transactionId, User $user)
    {
        return $this->baseRequest
            ->patch($this->apiUrl . '/transaction/' . $transactionId, [
                'action' => 'cancel',
                'cancel_information' => [
                    'cancellation_reason' => 'Transaction canceled by ' . $user->email
                ]
            ]);
    }

    public function getLinkToTransaction($transactionId)
    {
        return $this->escrowMyUrl . '/myescrow/Transaction.asp?TID=' . $transactionId;
    }

    public function newContractsCount($userId)
    {
        return $this->model->where('status', ContractConstant::STATUS_NEW)
            ->where(function ($query) use ($userId) {
                $query->where(function ($query) use ($userId) {
                    $query->where('buyer_id', $userId)
                        ->where(function ($query) {
                            $query->whereNull('params->viewedByBuyer')
                                ->orWhere('params->viewedByBuyer', false);
                        });
                })
                ->orWhere(function ($query) use ($userId) {
                    $query->where('seller_id', $userId)
                        ->where(function ($query) {
                            $query->whereNull('params->viewedBySeller')
                                ->orWhere('params->viewedBySeller', false);
                        });
                });
            })
            ->count();
    }

    public function openContractsCount($userId)
    {
        return $this->model->forUser($userId)
            ->whereIn('status', [ContractConstant::STATUS_NEW, ContractConstant::STATUS_AWAITING_PAYMENT, ContractConstant::STATUS_ON_PROGRESS, ContractConstant::STATUS_ON_CHECK])
            ->count();
    }

    public function search(array $params = [], array $columns = ['*'])
    {
        return $this->model
            ->query()
            ->select($columns)
            ->when(isset($params['status_not']), fn ($query) => $query->whereNotIn('status', $params['status_not']))
            ->when(isset($params['status']), fn ($query) => $query->whereIn('status', $params['status']));
    }
}
