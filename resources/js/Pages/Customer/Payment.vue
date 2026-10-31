<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatDateTime } from '@/lib/formatDate';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const receiptInput = ref<HTMLInputElement | null>(null);

interface Receipt {
    id: number;
    original_name: string;
    created_at: string;
}

const props = defineProps<{
    booking: { status: string; payment_option: string | null };
    fullAmountSen: number;
    depositAmountSen: number;
    balanceDueSen: number;
    manual: {
        bank_name: string | null;
        account_name: string | null;
        account_number: string | null;
        qr_image_url: string | null;
        instructions: { en: string; ms: string };
    };
    receipts: Receipt[];
}>();

const isBalanceMode = props.booking.status === 'reserved';

const form = useForm({
    payment_option: props.booking.payment_option ?? '',
});

const money = (sen: number) => (sen / 100).toFixed(2);
const submit = () => form.post(route('customer.payment'), { onSuccess: () => (changingOption.value = false) });

const changingOption = ref(false);

const amountDueSen = isBalanceMode
    ? props.balanceDueSen
    : props.booking.payment_option === 'full'
      ? props.fullAmountSen
      : props.depositAmountSen;

const receiptForm = useForm<{ receipt: File | null }>({ receipt: null });

const onReceiptChange = (event: Event) => {
    receiptForm.receipt = (event.target as HTMLInputElement).files?.[0] ?? null;
};

const uploadReceipt = () => {
    receiptForm.post(route('customer.payment.receipt'), {
        onSuccess: () => {
            receiptForm.reset();

            if (receiptInput.value) {
                receiptInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <CustomerLayout>
        <Head title="Payment" />

        <h1 class="mb-4 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Payment</h1>

        <form v-if="!isBalanceMode && (!booking.payment_option || changingOption)" class="space-y-3" @submit.prevent="submit">
            <label
                class="flex items-center justify-between rounded-md border p-3"
                :class="
                    form.payment_option === 'full'
                        ? 'border-indigo-500'
                        : 'border-gray-200 dark:border-gray-800 dark:bg-gray-900'
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
                        : 'border-gray-200 dark:border-gray-800 dark:bg-gray-900'
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

            <div class="flex items-center gap-3">
                <PrimaryButton :disabled="!form.payment_option || form.processing"
                    >Continue</PrimaryButton
                >
                <SecondaryButton v-if="changingOption" type="button" @click="changingOption = false">
                    Cancel
                </SecondaryButton>
            </div>
        </form>

        <div v-else class="space-y-4">
            <p v-if="isBalanceMode" class="text-sm text-gray-600 dark:text-gray-400">
                Your package is reserved. Please pay the remaining balance of
                <strong>RM {{ money(amountDueSen) }}</strong> before it can start, using the
                details below.
            </p>

            <template v-else>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    You chose
                    <strong>{{ booking.payment_option === 'full' ? 'full payment' : 'deposit' }}</strong
                    >. Please transfer
                    <strong>RM {{ money(amountDueSen) }}</strong>
                    using the details below, then wait for admin to confirm your payment.
                </p>

                <button
                    type="button"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    @click="changingOption = true"
                >
                    Change payment option
                </button>
            </template>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                <p><span class="text-gray-600">Bank:</span> {{ manual.bank_name }}</p>
                <p><span class="text-gray-600">Account name:</span> {{ manual.account_name }}</p>
                <p>
                    <span class="text-gray-600">Account number:</span> {{ manual.account_number }}
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

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="font-display font-bold text-gray-900 dark:text-gray-50">
                    Upload payment receipt
                </h2>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Upload a screenshot or PDF of your transfer receipt so we can verify it faster.
                </p>

                <ul v-if="receipts.length" class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                        v-for="receipt in receipts"
                        :key="receipt.id"
                        class="flex items-center justify-between py-2"
                    >
                        <span class="text-gray-700 dark:text-gray-300">{{ receipt.original_name }}</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ formatDateTime(receipt.created_at) }}</span>
                    </li>
                </ul>

                <form class="mt-3 flex items-center gap-3" @submit.prevent="uploadReceipt">
                    <input
                        ref="receiptInput"
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="hidden"
                        @change="onReceiptChange"
                    />
                    <SecondaryButton type="button" @click="receiptInput?.click()">
                        Choose file
                    </SecondaryButton>
                    <span class="flex-1 truncate text-gray-600 dark:text-gray-400">
                        {{ receiptForm.receipt?.name ?? 'No file chosen' }}
                    </span>
                    <PrimaryButton type="submit" :disabled="!receiptForm.receipt || receiptForm.processing">
                        Upload
                    </PrimaryButton>
                </form>
                <InputError :message="receiptForm.errors.receipt" />
            </div>
        </div>
    </CustomerLayout>
</template>
