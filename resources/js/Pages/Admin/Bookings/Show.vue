<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatDate, formatDateTime } from '@/lib/formatDate';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Payment {
    id: number;
    type: string;
    amount_sen: number;
    payment_date: string;
    method: string | null;
    reference: string | null;
    recorder: { name: string } | null;
}

interface PaymentReceipt {
    id: number;
    original_name: string;
    created_at: string;
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
    to_variant: { birth_type: string };
    consent: { expires_at: string; decision: string | null } | null;
}

interface Pause {
    id: number;
    from_date: string;
    to_date: string;
    reason: string;
    limit_overridden: boolean;
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
    payment_receipts: PaymentReceipt[];
    refunds: Refund[];
    plan_changes: PlanChange[];
    pauses: Pause[];
}

const props = defineProps<{
    booking: Booking;
    amountPaid: number;
    balanceDue: number;
}>();

const money = (sen: number) => (sen / 100).toFixed(2);

const statusTones: Record<string, 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'neutral'> = {
    waitlisted: 'neutral',
    pending_approval: 'warning',
    rejected: 'danger',
    awaiting_payment: 'info',
    expired: 'neutral',
    reserved: 'accent',
    active: 'success',
    paused: 'warning',
    completed: 'neutral',
    cancelled: 'danger',
};

const statusLabel = (status: string) =>
    status
        .split('_')
        .map((word) => word[0].toUpperCase() + word.slice(1))
        .join(' ');

const INACTIVE_STATUSES = ['cancelled', 'completed', 'rejected', 'expired'];
const isInactive = (status: string) => INACTIVE_STATUSES.includes(status);

const approve = () => useForm({}).post(route('admin.booking-approvals.approve', props.booking.id));

const rejecting = ref(false);
const rejectForm = useForm({ reason: '' });

const reject = () => {
    rejectForm.post(route('admin.booking-approvals.reject', props.booking.id), {
        onSuccess: () => {
            rejecting.value = false;
            rejectForm.reset();
        },
    });
};

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

const pauseForm = useForm({
    days: 1,
    reason: 'normal' as 'normal' | 'medical',
    override_limit: false,
});

const pause = () => {
    pauseForm.post(route('admin.bookings.pause', props.booking.id), {
        onSuccess: () => pauseForm.reset(),
    });
};

const resume = () => useForm({}).post(route('admin.bookings.resume', props.booking.id));

const cancelForm = useForm({
    type: 'normal' as 'normal' | 'compassionate' | 'do_not_contact',
    reason: '',
    override_amount: '',
});

const cancel = () => {
    if (! confirm('Cancel this booking? This cannot be undone.')) {
        return;
    }

    cancelForm.post(route('admin.bookings.cancel', props.booking.id), {
        onSuccess: () => cancelForm.reset(),
    });
};

const extendForm = useForm({ days: 1 });

const extend = () => {
    extendForm.post(route('admin.bookings.extend', props.booking.id), {
        onSuccess: () => extendForm.reset(),
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Booking ${booking.ref_no}`" />

        <div class="mb-6 flex items-center gap-3">
            <h1 class="font-mono text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ booking.ref_no }}
            </h1>
            <StatusBadge :tone="statusTones[booking.status] ?? 'neutral'">{{ statusLabel(booking.status) }}</StatusBadge>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Customer</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.email }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.phone }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Plan</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.variant.package.name.en }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.expected_birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                    · {{ booking.duration_days }} days ·
                    {{ booking.meal_plan === 'lunch_dinner' ? 'Lunch + Dinner' : 'Lunch only' }}
                    <span v-if="booking.is_twins">· Twins</span>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">EDD: {{ formatDate(booking.edd) }}</p>
                <p v-if="booking.start_date" class="text-sm text-gray-600 dark:text-gray-400">
                    Start {{ formatDate(booking.start_date) }} → End {{ formatDate(booking.end_date) }}
                </p>
                <p class="font-mono text-sm font-semibold tabular-nums text-gray-900 dark:text-gray-100">
                    RM {{ money(booking.price_sen) }} (paid RM {{ money(amountPaid) }}, balance RM
                    {{ money(balanceDue) }})
                </p>
            </div>

            <div v-if="booking.address" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Delivery</h2>
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
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Add-ons</h2>
                <p
                    v-for="(line, i) in booking.addons"
                    :key="i"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >
                    {{ line.addon.name.en }} × {{ line.qty }} — RM {{ money(line.price_sen) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Status</h2>
                <StatusBadge :tone="statusTones[booking.status] ?? 'neutral'">{{ statusLabel(booking.status) }}</StatusBadge>
                <p v-if="booking.approver" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Approved by {{ booking.approver.name }}
                </p>
                <p v-if="booking.rejection_reason" class="text-sm text-red-600 dark:text-red-400">
                    {{ booking.rejection_reason }}
                </p>

                <div v-if="booking.status === 'pending_approval'" class="mt-3 space-y-3">
                    <div class="flex items-center gap-2">
                        <PrimaryButton type="button" @click="approve">Approve</PrimaryButton>
                        <DangerButton type="button" @click="rejecting = !rejecting">Reject</DangerButton>
                    </div>

                    <div v-if="rejecting" class="flex items-center gap-2">
                        <TextInput
                            v-model="rejectForm.reason"
                            type="text"
                            placeholder="Reason for rejection"
                            class="flex-1"
                        />
                        <DangerButton type="button" @click="reject">Confirm reject</DangerButton>
                    </div>
                </div>
            </div>

            <div
                v-if="!isInactive(booking.status) || booking.payments.length"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Payments</h2>
                <table v-if="booking.payments.length" class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="payment in booking.payments" :key="payment.id">
                            <td class="whitespace-nowrap py-1 text-gray-600 dark:text-gray-400">
                                {{ formatDate(payment.payment_date) }}
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
                            <td class="py-1 text-right font-mono tabular-nums text-gray-900 dark:text-gray-100">
                                RM {{ money(payment.amount_sen) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-sm text-gray-600">No payments recorded yet.</p>

                <form
                    v-if="!isInactive(booking.status)"
                    class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-5 dark:border-gray-800"
                    @submit.prevent="recordPayment"
                >
                    <div>
                        <InputLabel for="pay-type" value="Type" />
                        <SelectInput id="pay-type" v-model="paymentForm.type" class="mt-1">
                            <option value="full">Full</option>
                            <option value="deposit">Deposit</option>
                            <option value="balance">Balance</option>
                            <option value="plan_change">Plan change</option>
                            <option value="extension">Extension</option>
                        </SelectInput>
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
                v-if="booking.payment_receipts.length"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                    Receipts uploaded by customer
                </h2>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <li
                        v-for="receipt in booking.payment_receipts"
                        :key="receipt.id"
                        class="flex items-center justify-between py-1.5"
                    >
                        <span class="text-gray-700 dark:text-gray-300">{{ receipt.original_name }}</span>
                        <span class="flex items-center gap-3">
                            <span class="text-gray-600 dark:text-gray-400">{{ formatDateTime(receipt.created_at) }}</span>
                            <a
                                :href="route('admin.bookings.receipts.download', [booking.id, receipt.id])"
                                class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                            >
                                Download
                            </a>
                        </span>
                    </li>
                </ul>
            </div>

            <div
                v-if="booking.plan_changes.length"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Plan changes</h2>
                <div
                    v-for="pc in booking.plan_changes"
                    :key="pc.id"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >
                    To {{ pc.to_variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}, RM
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
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Refunds</h2>
                <div v-for="refund in booking.refunds" :key="refund.id" class="mb-3 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ refund.reason }} — RM {{ money(refund.amount_sen) }} —
                        {{ refund.status }}
                    </p>
                    <div v-if="refund.status === 'pending'" class="mt-2 flex flex-wrap items-center gap-2">
                        <TextInput
                            v-model="refundForm(refund.id).method"
                            type="text"
                            placeholder="Method"
                            class="w-36"
                        />
                        <TextInput
                            v-model="refundForm(refund.id).reference"
                            type="text"
                            placeholder="Reference"
                            class="w-40"
                        />
                        <input
                            type="file"
                            class="text-sm text-gray-600 dark:text-gray-400"
                            @change="(e) => onProofChange(refund.id, e)"
                        />
                        <SecondaryButton @click="markRefundPaid(refund.id)"
                            >Mark paid</SecondaryButton
                        >
                    </div>
                </div>
            </div>

            <div
                v-if="['active', 'paused'].includes(booking.status)"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Pause / resume / extend</h2>

                <div v-if="booking.pauses.length" class="mb-3 space-y-1">
                    <p v-for="p in booking.pauses" :key="p.id" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ formatDate(p.from_date) }} → {{ formatDate(p.to_date) }} ({{ p.reason }}<span v-if="p.limit_overridden">, override</span>)
                    </p>
                </div>

                <SecondaryButton v-if="booking.status === 'paused'" @click="resume">Resume now</SecondaryButton>

                <form v-else class="flex flex-wrap items-end gap-3" @submit.prevent="pause">
                    <div>
                        <InputLabel for="pause-days" value="Pause for (days)" />
                        <TextInput id="pause-days" v-model="pauseForm.days" type="number" min="1" class="mt-1 block w-24" />
                    </div>
                    <div>
                        <InputLabel for="pause-reason" value="Reason" />
                        <SelectInput id="pause-reason" v-model="pauseForm.reason" class="mt-1">
                            <option value="normal">Normal</option>
                            <option value="medical">Medical</option>
                        </SelectInput>
                    </div>
                    <label class="flex items-center gap-2 pb-2 text-sm text-gray-700 dark:text-gray-300">
                        <Checkbox v-model:checked="pauseForm.override_limit" />
                        Override pause limit
                    </label>
                    <PrimaryButton :disabled="pauseForm.processing">Pause</PrimaryButton>
                    <InputError :message="pauseForm.errors.days" />
                </form>

                <form class="mt-4 flex items-end gap-3" @submit.prevent="extend">
                    <div>
                        <InputLabel for="extend-days" value="Extend by (days)" />
                        <TextInput id="extend-days" v-model="extendForm.days" type="number" min="1" class="mt-1 block w-24" />
                    </div>
                    <SecondaryButton :disabled="extendForm.processing" type="submit">Extend</SecondaryButton>
                </form>
            </div>

            <div
                v-if="!isInactive(booking.status)"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2"
            >
                <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Cancel booking</h2>

                <form class="flex flex-wrap items-end gap-3" @submit.prevent="cancel">
                    <div>
                        <InputLabel for="cancel-type" value="Type" />
                        <SelectInput id="cancel-type" v-model="cancelForm.type" class="mt-1">
                            <option value="normal">Normal</option>
                            <option value="compassionate">Compassionate</option>
                            <option value="do_not_contact">Do not contact</option>
                        </SelectInput>
                    </div>
                    <div class="min-w-[16rem] flex-1">
                        <InputLabel for="cancel-reason" value="Reason (internal)" />
                        <TextInput id="cancel-reason" v-model="cancelForm.reason" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="cancel-override" value="Override refund (RM, optional)" />
                        <TextInput
                            id="cancel-override"
                            v-model="cancelForm.override_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-32"
                        />
                    </div>
                    <PrimaryButton :disabled="cancelForm.processing">Cancel booking</PrimaryButton>
                    <InputError :message="cancelForm.errors.type" />
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
