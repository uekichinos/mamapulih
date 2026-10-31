<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    settings: {
        deposit_type: 'fixed' | 'percentage';
        deposit_amount: number;
        balance_due_days_before_start: number;
        start_notice_days: number;
        approval_hold_hours: number;
        payment_deadline_hours: number;
        consent_expiry_hours: number;
        regular_capacity: number;
        buffer_capacity: number;
        twins_slots: 1 | 2;
        pause_limit_count: number;
    };
}>();

const form = useForm({ ...props.settings });

const submit = () => form.put(route('admin.settings.update'));
</script>

<template>
    <AdminLayout>
        <Head title="Booking settings" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Settings
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="deposit_type" value="Deposit type" />
                    <SelectInput id="deposit_type" v-model="form.deposit_type" class="mt-1">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed (RM)</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.deposit_type" />
                </div>

                <div>
                    <InputLabel
                        for="deposit_amount"
                        :value="form.deposit_type === 'percentage' ? 'Deposit (%)' : 'Deposit (RM)'"
                    />
                    <TextInput
                        id="deposit_amount"
                        v-model.number="form.deposit_amount"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.deposit_amount" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel
                        for="balance_due_days_before_start"
                        value="Balance due (days before start)"
                    />
                    <TextInput
                        id="balance_due_days_before_start"
                        v-model.number="form.balance_due_days_before_start"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.balance_due_days_before_start" />
                </div>

                <div>
                    <InputLabel for="start_notice_days" value="Minimum start notice (days)" />
                    <TextInput
                        id="start_notice_days"
                        v-model.number="form.start_notice_days"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.start_notice_days" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="approval_hold_hours" value="Approval hold time (hours)" />
                    <TextInput
                        id="approval_hold_hours"
                        v-model.number="form.approval_hold_hours"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.approval_hold_hours" />
                </div>

                <div>
                    <InputLabel for="payment_deadline_hours" value="Payment deadline (hours)" />
                    <TextInput
                        id="payment_deadline_hours"
                        v-model.number="form.payment_deadline_hours"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.payment_deadline_hours" />
                </div>

                <div>
                    <InputLabel for="consent_expiry_hours" value="Consent link expiry (hours)" />
                    <TextInput
                        id="consent_expiry_hours"
                        v-model.number="form.consent_expiry_hours"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.consent_expiry_hours" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="regular_capacity" value="Regular capacity / day" />
                    <TextInput
                        id="regular_capacity"
                        v-model.number="form.regular_capacity"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.regular_capacity" />
                </div>

                <div>
                    <InputLabel for="buffer_capacity" value="Buffer capacity / day" />
                    <TextInput
                        id="buffer_capacity"
                        v-model.number="form.buffer_capacity"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.buffer_capacity" />
                </div>

                <div>
                    <InputLabel for="twins_slots" value="Twins use how many slots" />
                    <SelectInput id="twins_slots" v-model="form.twins_slots" class="mt-1">
                        <option :value="1">1 slot</option>
                        <option :value="2">2 slots</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.twins_slots" />
                </div>

                <div>
                    <InputLabel for="pause_limit_count" value="Pause limit (times)" />
                    <TextInput
                        id="pause_limit_count"
                        v-model.number="form.pause_limit_count"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.pause_limit_count" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Save
                </PrimaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                    Saved.
                </p>
            </div>
        </form>
    </AdminLayout>
</template>
