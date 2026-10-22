<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Payment {
    id: number;
    type: string;
    amount_sen: number;
    payment_date: string;
    method: string | null;
    reference: string | null;
    recorder: { name: string } | null;
}

interface Refund {
    id: number;
    reason: string;
    amount_sen: number;
    status: string;
}

interface PlanChange {
    id: number;
    status: string;
    amount_sen: number;
    toVariant: { birth_type: string };
    consent: { expires_at: string; decision: string | null } | null;
}

interface Booking {
    id: number;
    ref_no: string;
    status: string;
    edd: string;
    expected_birth_type: string;
    actual_birth_type: string | null;
    start_date: string | null;
    end_date: string | null;
    duration_days: number;
    meal_plan: string;
    is_twins: boolean;
    price_sen: number;
    rejection_reason: string | null;
    customer: { name: string; email: string | null; phone: string | null };
    variant: { package: { name: Record<string, string> } };
    address: {
        line1: string;
        line2: string | null;
        postcode: string;
        zone: { name: string };
    } | null;
    addons: { addon: { name: Record<string, string> }; qty: number; price_sen: number }[];
    approver: { name: string } | null;
    payments: Payment[];
    refunds: Refund[];
    planChanges: PlanChange[];
}

const props = defineProps<{
    booking: Booking;
    amountPaid: number;
    balanceDue: number;
}>();

const money = (sen: number) => (sen / 100).toFixed(2);

const paymentForm = useForm({
    type: 'full',
    amount: '',
    payment_date: new Date().toISOString().slice(0, 10),
    method: '',
    reference: '',
    notes: '',
});

const recordPayment = () => {
    paymentForm.post(route('admin.bookings.payments.store', props.booking.id), {
        onSuccess: () => paymentForm.reset('amount', 'method', 'reference', 'notes'),
    });
};

const refundForms: Record<number, ReturnType<typeof useForm>> = {};
const refundForm = (refundId: number) => {
    refundForms[refundId] ??= useForm({ method: '', reference: '', proof: null as File | null });

    return refundForms[refundId];
};

const onProofChange = (refundId: number, event: Event) => {
    refundForm(refundId).proof = (event.target as HTMLInputElement).files?.[0] ?? null;
};

const markRefundPaid = (refundId: number) => {
    refundForm(refundId).post(
        route('admin.bookings.refunds.mark-paid', [props.booking.id, refundId]),
    );
};

const resendConsent = () =>
    useForm({}).post(route('admin.bookings.resend-consent', props.booking.id));
</script>

<template>
    <AdminLayout>
        <Head :title="`Booking ${booking.ref_no}`" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Booking {{ booking.ref_no }}
        </h1>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Customer</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.email }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.phone }}</p>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Plan</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.variant.package.name.en }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.expected_birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                    · {{ booking.duration_days }} days ·
                    {{ booking.meal_plan === 'lunch_dinner' ? 'Lunch + Dinner' : 'Lunch only' }}
                    <span v-if="booking.is_twins">· Twins</span>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">EDD: {{ booking.edd }}</p>
                <p v-if="booking.start_date" class="text-sm text-gray-600 dark:text-gray-400">
                    Start {{ booking.start_date }} → End {{ booking.end_date }}
                </p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    RM {{ money(booking.price_sen) }} (paid RM {{ money(amountPaid) }}, balance RM
                    {{ money(balanceDue) }})
                </p>
            </div>

            <div v-if="booking.address" class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Delivery</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.address.line1
                    }}<span v-if="booking.address.line2">, {{ booking.address.line2 }}</span
                    >,
                    {{ booking.address.postcode }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zone: {{ booking.address.zone.name }}
                </p>
            </div>

            <div
                v-if="booking.addons.length"
                class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Add-ons</h2>
                <p
                    v-for="(line, i) in booking.addons"
                    :key="i"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >
                    {{ line.addon.name.en }} × {{ line.qty }} — RM {{ money(line.price_sen) }}
                </p>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Status</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.status }}</p>
                <p v-if="booking.approver" class="text-sm text-gray-600 dark:text-gray-400">
                    Approved by {{ booking.approver.name }}
                </p>
                <p v-if="booking.rejection_reason" class="text-sm text-red-600 dark:text-red-400">
                    {{ booking.rejection_reason }}
                </p>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:col-span-2">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Payments</h2>
                <table v-if="booking.payments.length" class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="payment in booking.payments" :key="payment.id">
                            <td class="py-1 text-gray-600 dark:text-gray-400">
                                {{ payment.payment_date }}
                            </td>
                            <td class="py-1 text-gray-600 dark:text-gray-400">
                                {{ payment.type }}
                            </td>
                            <td class="py-1 text-gray-600 dark:text-gray-400">
                                {{ payment.method }}
                            </td>
                            <td class="py-1 text-gray-600 dark:text-gray-400">
                                {{ payment.reference }}
                            </td>
                            <td class="py-1 text-right text-gray-900 dark:text-gray-100">
                                RM {{ money(payment.amount_sen) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-sm text-gray-500">No payments recorded yet.</p>

                <form
                    class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5"
                    @submit.prevent="recordPayment"
                >
                    <div>
                        <InputLabel for="pay-type" value="Type" />
                        <select
                            id="pay-type"
                            v-model="paymentForm.type"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="full">Full</option>
                            <option value="deposit">Deposit</option>
                            <option value="balance">Balance</option>
                            <option value="plan_change">Plan change</option>
                            <option value="extension">Extension</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="pay-amount" value="Amount (RM)" />
                        <TextInput
                            id="pay-amount"
                            v-model="paymentForm.amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="paymentForm.errors.amount" />
                    </div>
                    <div>
                        <InputLabel for="pay-date" value="Date" />
                        <TextInput
                            id="pay-date"
                            v-model="paymentForm.payment_date"
                            type="date"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="pay-method" value="Method" />
                        <TextInput
                            id="pay-method"
                            v-model="paymentForm.method"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="pay-reference" value="Reference" />
                        <TextInput
                            id="pay-reference"
                            v-model="paymentForm.reference"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="col-span-2 sm:col-span-5">
                        <PrimaryButton :disabled="paymentForm.processing"
                            >Record payment</PrimaryButton
                        >
                    </div>
                </form>
            </div>

            <div
                v-if="booking.planChanges.length"
                class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:col-span-2"
            >
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Plan changes</h2>
                <div
                    v-for="pc in booking.planChanges"
                    :key="pc.id"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >
                    To {{ pc.toVariant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}, RM
                    {{ money(pc.amount_sen) }} — {{ pc.status }}
                    <SecondaryButton
                        v-if="pc.status === 'awaiting_consent'"
                        class="ml-2"
                        @click="resendConsent"
                    >
                        Resend link
                    </SecondaryButton>
                </div>
            </div>

            <div
                v-if="booking.refunds.length"
                class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:col-span-2"
            >
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Refunds</h2>
                <div v-for="refund in booking.refunds" :key="refund.id" class="mb-3 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ refund.reason }} — RM {{ money(refund.amount_sen) }} —
                        {{ refund.status }}
                    </p>
                    <div v-if="refund.status === 'pending'" class="mt-2 flex items-center gap-2">
                        <input
                            v-model="refundForm(refund.id).method"
                            type="text"
                            placeholder="Method"
                            class="rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                        <input
                            v-model="refundForm(refund.id).reference"
                            type="text"
                            placeholder="Reference"
                            class="rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                        <input
                            type="file"
                            class="text-sm"
                            @change="(e) => onProofChange(refund.id, e)"
                        />
                        <SecondaryButton @click="markRefundPaid(refund.id)"
                            >Mark paid</SecondaryButton
                        >
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
