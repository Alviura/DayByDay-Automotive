<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificationRecipientService
{
    public function __construct(
        private TransferRequestAccessService $transferRequests,
    ) {}

    /**
     * @param  Collection<int, User>|array<int, User>  $users
     */
    public function notifyMany(Collection|array $users, Notification $notification): void
    {
        foreach (collect($users)->unique('id') as $user) {
            $user->notify($notification);
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithPermission(string $permission): Collection
    {
        return User::permission($permission)->active()->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function reviewersForTransferRequest(TransferRequest $request): Collection
    {
        return $this->usersWithPermission('transfer_requests.review')
            ->filter(fn (User $user) => $this->transferRequests->canReview($user, $request));
    }

    /**
     * @return Collection<int, User>
     */
    public function receiversForTransfer(StockTransfer $transfer): Collection
    {
        $users = User::query()->active()->get()->filter(
            fn (User $user) => app(StockTransferAccessService::class)->canReceive($user, $transfer)
        );

        return $users->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function inventoryStakeholders(): Collection
    {
        return $this->usersWithPermission('inventory.view');
    }

    /**
     * @return Collection<int, User>
     */
    public function procurementStakeholders(): Collection
    {
        return $this->usersWithPermission('procurement.view');
    }

    /**
     * @return Collection<int, User>
     */
    public function usersAtLocation(Shop|Warehouse $location): Collection
    {
        if ($location instanceof Shop) {
            return User::query()
                ->active()
                ->where('shop_id', $location->id)
                ->get();
        }

        return User::query()
            ->active()
            ->where('warehouse_id', $location->id)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function sourceStakeholdersForTransfer(StockTransfer $transfer): Collection
    {
        $transfer->loadMissing(['source']);

        $users = collect();

        if ($transfer->source instanceof Shop || $transfer->source instanceof Warehouse) {
            $users = $users->merge($this->usersAtLocation($transfer->source));
        }

        if ($transfer->dispatched_by) {
            $dispatcher = User::query()->active()->find($transfer->dispatched_by);
            if ($dispatcher) {
                $users->push($dispatcher);
            }
        }

        return $users->unique('id')->values();
    }
}
