<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    found: boolean;
    decided: boolean;
    expired: boolean;
    decision: string | null;
    token: string;
    planChange: {
        amount_sen: number;
        from_variant: { birth_type: string; duration_days: number };
        to_variant: { birth_type: string; duration_days: number };
    } | null;
}>();

const form = useForm({ decision: '' });
const money = (sen: number) => (Math.abs(sen) / 100).toFixed(2);

const decide = (decision: 'agree' | 'decline') => {
    form.decision = decision;
    form.post(route('customer.consent.store', { token: props.token }));
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm your plan" />

        <div v-if="!found" class="text-center text-sm text-gray-600 dark:text-gray-400">
            This link is not valid.
        </div>

        <div v-else-if="expired" class="text-center text-sm text-gray-600 dark:text-gray-400">
            This link has expired. Please contact us so we can send a new one.
        </div>

        <div v-else-if="decided" class="text-center text-sm text-gray-600 dark:text-gray-400">
            <p v-if="decision === 'agree'">Thank you — you've agreed to this plan change.</p>
            <p v-else>You've declined this plan change. Your original plan stays as is.</p>
        </div>

        <div v-else-if="planChange" class="space-y-4">
            <h1 class="font-display text-lg font-extrabold text-gray-900 dark:text-gray-50">
                Confirm your plan
            </h1>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Your baby's birth type was
                {{ planChange.from_variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                in your booking, but turned out to be
                {{ planChange.to_variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}.
            </p>

            <p class="text-sm text-gray-700 dark:text-gray-300">
                <span v-if="planChange.amount_sen > 0">
                    This adds RM {{ money(planChange.amount_sen) }} to your booking.
                </span>
                <span v-else-if="planChange.amount_sen < 0">
                    This refunds RM {{ money(planChange.amount_sen) }} to you.
                </span>
                <span v-else>There is no price change.</span>
            </p>

            <div class="flex gap-3">
                <PrimaryButton :disabled="form.processing" @click="decide('agree')"
                    >Agree</PrimaryButton
                >
                <SecondaryButton :disabled="form.processing" @click="decide('decline')"
                    >Decline</SecondaryButton
                >
            </div>
        </div>
    </GuestLayout>
</template>
