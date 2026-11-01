<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
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

const sections = [
    {
        key: 'deposit',
        title: 'Deposit',
        description: 'How much a customer must pay upfront to reserve a slot.',
        tone: 'indigo',
        icon: 'M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z',
    },
    {
        key: 'deadlines',
        title: 'Deadlines & timing',
        description:
            'When an unconfirmed booking expires and releases its slot, and how much notice a start date needs.',
        tone: 'amber',
        icon: 'M12 7v5l3 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    },
    {
        key: 'capacity',
        title: 'Capacity',
        description: 'How many mothers can be taken on per day.',
        tone: 'blue',
        icon: 'M17 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M15.5 3.5a3.5 3.5 0 1 1 0 7M20.5 20v-1a4 4 0 0 0-3-3.87M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z',
    },
    {
        key: 'pauses',
        title: 'Pauses',
        description: 'How many times an active booking can be paused.',
        tone: 'green',
        icon: 'M10 4v16M14 4v16',
    },
] as const;
</script>

<template>
    <AdminLayout>
        <Head title="Booking settings" />

        <PageHeader title="Settings">
            Pricing, deadlines and capacity rules applied to every booking.
        </PageHeader>

        <form class="space-y-4" @submit.prevent="submit">
            <SectionCard
                :title="sections[0].title"
                :description="sections[0].description"
                :tone="sections[0].tone"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path :d="sections[0].icon" />
                    </svg>
                </template>

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
                            :value="
                                form.deposit_type === 'percentage' ? 'Deposit (%)' : 'Deposit (RM)'
                            "
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
            </SectionCard>

            <SectionCard
                :title="sections[1].title"
                :description="sections[1].description"
                :tone="sections[1].tone"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path :d="sections[1].icon" />
                    </svg>
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="approval_hold_hours" value="Approval hold (hours)" />
                        <TextInput
                            id="approval_hold_hours"
                            v-model.number="form.approval_hold_hours"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            A new booking expires if not approved or rejected in time.
                        </p>
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
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            An approved booking expires if not paid in time.
                        </p>
                        <InputError class="mt-2" :message="form.errors.payment_deadline_hours" />
                    </div>

                    <div>
                        <InputLabel
                            for="consent_expiry_hours"
                            value="Consent link expiry (hours)"
                        />
                        <TextInput
                            id="consent_expiry_hours"
                            v-model.number="form.consent_expiry_hours"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            How long a plan-change consent link stays valid.
                        </p>
                        <InputError class="mt-2" :message="form.errors.consent_expiry_hours" />
                    </div>

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
                        <InputError
                            class="mt-2"
                            :message="form.errors.balance_due_days_before_start"
                        />
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
            </SectionCard>

            <SectionCard
                :title="sections[2].title"
                :description="sections[2].description"
                :tone="sections[2].tone"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path :d="sections[2].icon" />
                    </svg>
                </template>

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
                </div>
            </SectionCard>

            <SectionCard
                :title="sections[3].title"
                :description="sections[3].description"
                :tone="sections[3].tone"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path :d="sections[3].icon" />
                    </svg>
                </template>

                <div class="max-w-[12rem]">
                    <InputLabel for="pause_limit_count" value="Pause limit (times)" />
                    <TextInput
                        id="pause_limit_count"
                        v-model.number="form.pause_limit_count"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.pause_limit_count" />
                </div>
            </SectionCard>

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
