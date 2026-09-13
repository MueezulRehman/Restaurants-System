<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'reservations' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->unsignedBigInteger('customer_id')->nullable();
                    $table->unsignedBigInteger('table_id')->nullable();
                    $table->string('guest_name');
                    $table->string('guest_phone')->nullable();
                    $table->unsignedInteger('party_size')->default(1);
                    $table->dateTime('starts_at');
                    $table->dateTime('ends_at')->nullable();
                    $table->string('status', 30)->default('requested');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->index(['restaurant_id', 'starts_at']);
                },
                'kitchen_tickets' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->unsignedBigInteger('order_id')->nullable();
                    $table->string('ticket_number', 40)->unique();
                    $table->string('station', 80)->nullable();
                    $table->string('status', 30)->default('queued');
                    $table->unsignedInteger('priority')->default(0);
                    $table->timestamp('started_at')->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->index(['restaurant_id', 'status', 'priority']);
                },
                'fitting_room_sessions' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->unsignedBigInteger('customer_id')->nullable();
                    $table->unsignedBigInteger('staff_id')->nullable();
                    $table->string('room_label', 80)->nullable();
                    $table->string('status', 30)->default('open');
                    $table->timestamp('started_at');
                    $table->timestamp('ended_at')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->index(['restaurant_id', 'status']);
                },
                'fitting_room_items' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('fitting_room_session_id');
                    $table->unsignedBigInteger('menu_item_id')->nullable();
                    $table->unsignedBigInteger('product_variant_id')->nullable();
                    $table->string('status', 30)->default('in_room');
                    $table->unsignedInteger('quantity')->default(1);
                    $table->timestamps();
                },
                'insurance_providers' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                },
                'insurance_claims' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->unsignedBigInteger('provider_id')->nullable();
                    $table->unsignedBigInteger('customer_id')->nullable();
                    $table->string('policy_number')->nullable();
                    $table->string('claim_number')->nullable();
                    $table->decimal('claimed_amount', 12, 2)->default(0);
                    $table->decimal('approved_amount', 12, 2)->nullable();
                    $table->string('status', 30)->default('draft');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->index(['restaurant_id', 'status']);
                },
                'controlled_medicine_logs' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->unsignedBigInteger('medicine_id');
                    $table->unsignedBigInteger('customer_id')->nullable();
                    $table->unsignedBigInteger('prescription_id')->nullable();
                    $table->unsignedBigInteger('dispensed_by')->nullable();
                    $table->decimal('quantity', 12, 3);
                    $table->string('witness_name')->nullable();
                    $table->text('reason')->nullable();
                    $table->timestamp('dispensed_at');
                    $table->timestamps();
                    $table->index(['restaurant_id', 'medicine_id', 'dispensed_at'], 'cmed_log_lookup_idx');
                },
                'custom_field_definitions' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->string('entity_type', 60);
                    $table->string('name');
                    $table->string('field_key', 80);
                    $table->string('field_type', 30)->default('text');
                    $table->json('options')->nullable();
                    $table->boolean('is_required')->default(false);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                    $table->unique(['restaurant_id', 'entity_type', 'field_key'], 'custom_field_key_unique');
                },
                'custom_field_values' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('custom_field_definition_id');
                    $table->unsignedBigInteger('entity_id');
                    $table->text('value')->nullable();
                    $table->timestamps();
                    $table->unique(['custom_field_definition_id', 'entity_id'], 'custom_field_value_unique');
                    $table->index('entity_id');
                },
                'workflow_definitions' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id')->index();
                    $table->string('entity_type', 60);
                    $table->string('name');
                    $table->json('statuses');
                    $table->json('transitions')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                },
                'workflow_records' => function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('workflow_definition_id');
                    $table->unsignedBigInteger('entity_id');
                    $table->string('status', 50);
                    $table->json('data')->nullable();
                    $table->timestamps();
                    $table->index(['workflow_definition_id', 'entity_id']);
                },
            ] as $tableName => $definition
        ) {
            if (! Schema::hasTable($tableName)) {
                Schema::create($tableName, $definition);
            }
        }
    }

    public function down(): void
    {
        foreach (['workflow_records', 'workflow_definitions', 'custom_field_values', 'custom_field_definitions', 'controlled_medicine_logs', 'insurance_claims', 'insurance_providers', 'fitting_room_items', 'fitting_room_sessions', 'kitchen_tickets', 'reservations'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
