<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    booking: { payment_option: string | null };
    fullAmountSen: number;
    depositAmountSen: number;
    manual: {
        bank_name: string | null;
        account_name: string | null;
        account_number: string | null;
        qr_image_url: string | null;
        instructions: { en: string; ms: string };
    };
}>();

const form = useForm({
    payment_option: props.booking.payment_option ?? '',
});

const money = (sen: number) => (sen / 100).toFixed(2);
const submit = () => form.post(route('customer.payment'));
</script>

<template>
    <CustomerLayout>
        <Head title="Payment" />

        <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">Payment</h1>

        <form v-if="!booking.payment_option" class="space-y-3" @submit.prevent="submit">
            <label
                class="flex items-center justify-between rounded-md border p-3"
                :class="
                    form.payment_option === 'full'
                        ? 'border-indigo-500'
                        : 'border-gray-200 dark:border-gray-700'
                "
            >
                <span class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input v-model="form.payment_option" type="radio" value="full" />
                    Full payment
                </span>
                <span class="font-medium text-gray-900 dark:text-gray-100"
                    >RM {{ money(fullAmountSen) }}</span
                >
            </label>

            <label
                class="flex items-center justify-between rounded-md border p-3"
                :class="
                    form.payment_option === 'deposit'
                        ? 'border-indigo-500'
                        : 'border-gray-200 dark:border-gray-700'
                "
            >
                <span class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input v-model="form.payment_option" type="radio" value="deposit" />
                    Deposit now, balance before start
                </span>
                <span class="font-medium text-gray-900 dark:text-gray-100"
                    >RM {{ money(depositAmountSen) }}</span
                >
            </label>

            <PrimaryButton :disabled="!form.payment_option || form.processing"
                >Continue</PrimaryButton
            >
        </form>

        <div v-else class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                You chose
                <strong>{{ booking.payment_option === 'full' ? 'full payment' : 'deposit' }}</strong
                >. Please transfer
                <strong
                    >RM
                    {{
                        money(booking.payment_option === 'full' ? fullAmountSen : depositAmountSen)
                    }}</strong
                >
                using the details below, then wait for admin to confirm your payment.
            </p>

            <div class="rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-700">
                <p><span class="text-gray-500">Bank:</span> {{ manual.bank_name }}</p>
                <p><span class="text-gray-500">Account name:</span> {{ manual.account_name }}</p>
                <p>
                    <span class="text-gray-500">Account number:</span> {{ manual.account_number }}
                </p>
                <img
                    v-if="manual.qr_image_url"
                    :src="manual.qr_image_url"
                    alt="DuitNow QR"
                    class="mt-3 h-40 w-40 object-contain"
                />
                <p
                    v-if="manual.instructions.en"
                    class="mt-3 whitespace-pre-line text-gray-600 dark:text-gray-400"
                >
                    {{ manual.instructions.en }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>
