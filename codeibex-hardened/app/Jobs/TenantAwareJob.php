<?php

namespace App\Jobs;

use App\Support\Tenancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Base job that automatically restores the correct tenant database context
 * when the job is processed by a queue worker.
 *
 * Usage:
 *   class SendOrderInvoice extends TenantAwareJob
 *   {
 *       public function __construct(public int $restaurantId, public int $orderId) {}
 *
 *       public function handleTenant(): void
 *       {
 *           $order = Order::findOrFail($this->orderId);
 *           // ... normal tenant-scoped code
 *       }
 *   }
 *
 * Always pass the restaurant id (or any identifier you need) in the constructor.
 *
 * @author Mueez Ul Rehman
 */
abstract class TenantAwareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The restaurant / tenant id this job belongs to.
     * Child classes must set this (usually via constructor promotion).
     */
    public int $restaurantId;

    /**
     * Final handle that sets up tenancy then calls handleTenant().
     */
    public function handle(): void
    {
        if (empty($this->restaurantId)) {
            throw new \RuntimeException(
                static::class . ' must define a public $restaurantId property before being queued.'
            );
        }

        try {
            Tenancy::forRestaurantId($this->restaurantId, function () {
                $this->handleTenant();
            });
        } catch (Throwable $e) {
            // Ensure we never leave a worker process stuck on a tenant connection
            Tenancy::end();
            throw $e;
        } finally {
            Tenancy::end();
        }
    }

    /**
     * Implement your real job logic here.
     * At this point the default DB connection is already the tenant database.
     */
    abstract public function handleTenant(): void;
}
