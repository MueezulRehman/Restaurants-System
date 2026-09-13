<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
                $table->string('guest_name');
                $table->string('guest_phone')->nullable();
                $table->unsignedInteger('party_size')->default(1);
                $table->dateTime('starts_at');
                $table->dateTime('ends_at')->nullable();
                $table->string('status', 30)->default('requested');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'starts_at']);
                $table->index(['restaurant_id', 'status']);
            });
        }

        if (! Schema::hasTable('kitchen_tickets')) {
            Schema::create('kitchen_tickets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ticket_number', 40)->unique();
                $table->string('station', 80)->nullable();
                $table->string('status', 30)->default('queued');
                $table->unsignedInteger('priority')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'status', 'priority']);
            });
        }

        if (! Schema::hasTable('fitting_room_sessions')) {
            Schema::create('fitting_room_sessions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('room_label', 80)->nullable();
                $table->string('status', 30)->default('open');
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'status']);
            });
        }

        if (! Schema::hasTable('fitting_room_items')) {
            Schema::create('fitting_room_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('fitting_room_session_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 30)->default('in_room');
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('insurance_providers')) {
            Schema::create('insurance_providers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('insurance_claims')) {
            Schema::create('insurance_claims', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('provider_id')->nullable()->constrained('insurance_providers')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('policy_number')->nullable();
                $table->string('claim_number')->nullable();
                $table->decimal('claimed_amount', 12, 2)->default(0);
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->string('status', 30)->default('draft');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'status']);
            });
        }

        if (! Schema::hasTable('controlled_medicine_logs')) {
            Schema::create('controlled_medicine_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->string('witness_name')->nullable();
                $table->text('reason')->nullable();
                $table->timestamp('dispensed_at');
                $table->timestamps();
                $table->index(['restaurant_id', 'medicine_id', 'dispensed_at'], 'cmed_log_lookup_idx');
            });
        }

        if (! Schema::hasTable('custom_field_definitions')) {
            Schema::create('custom_field_definitions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type', 60);
                $table->string('name');
                $table->string('field_key', 80);
                $table->string('field_type', 30)->default('text');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['restaurant_id', 'entity_type', 'field_key'], 'custom_field_key_unique');
            });
        }

        if (! Schema::hasTable('custom_field_values')) {
            Schema::create('custom_field_values', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('custom_field_definition_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('entity_id');
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['custom_field_definition_id', 'entity_id'], 'custom_field_value_unique');
                $table->index(['entity_id']);
            });
        }

        if (! Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type', 60);
                $table->string('name');
                $table->json('statuses');
                $table->json('transitions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('workflow_records')) {
            Schema::create('workflow_records', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('workflow_definition_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('entity_id');
                $table->string('status', 50);
                $table->json('data')->nullable();
                $table->timestamps();
                $table->index(['workflow_definition_id', 'entity_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_records');
        Schema::dropIfExists('workflow_definitions');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
        Schema::dropIfExists('controlled_medicine_logs');
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('insurance_providers');
        Schema::dropIfExists('fitting_room_items');
        Schema::dropIfExists('fitting_room_sessions');
        Schema::dropIfExists('kitchen_tickets');
        Schema::dropIfExists('reservations');
    }
};
