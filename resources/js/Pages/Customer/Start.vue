<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import AlertBanner from '@/Components/AlertBanner.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    booking: { id: number; edd: string; start_date: string | null };
    minStartDate: string;
    pendingConsent: boolean;
    balanceDue: number;
    canConfirm: boolean;
}>();

const defaultStartDate =
    props.booking.edd.slice(0, 10) > props.minStartDate
        ? props.booking.edd.slice(0, 10)
        : props.minStartDate;

const form = useForm({
    start_date: defaultStartDate,
    actual_birth_type: 'normal',
});

const money = (sen: number) => (sen / 100).toFixed(2);
const submit = () => form.post(route('customer.start', props.booking.id));
const confirm = () => useForm({}).post(route('customer.start.confirm', props.booking.id));
</script>

<template>
    <CustomerAppLayout>
        <Head title="Start my package" />

        <PageHeader title="Start my package" />

        <AlertBanner v-if="pendingConsent" tone="amber">
            We're waiting for you to review a plan change — check your email for the link.
        </AlertBanner>

        <AlertBanner v-else-if="booking.start_date && balanceDue > 0" tone="amber">
            <p>
                A balance of RM {{ money(balanceDue) }} is still due before your package can start.
            </p>
            <Link
                :href="route('customer.payment')"
                class="mt-2 inline-block font-semibold underline"
            >
                Pay balance
            </Link>
        </AlertBanner>

        <div
            v-else-if="booking.start_date && canConfirm"
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Start date: {{ booking.start_date }}. Everything is ready — confirm to begin
                deliveries.
            </p>
            <SecondaryButton @click="confirm">Confirm start</SecondaryButton>
        </div>

        <form
            v-else
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="start_date" value="Start date" />
                <TextInput
                    id="start_date"
                    v-model="form.start_date"
                    type="date"
                    :min="minStartDate"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.start_date" />
            </div>

            <div>
                <InputLabel for="actual_birth_type" value="Actual birth type" />
                <select
                    id="actual_birth_type"
                    v-model="form.actual_birth_type"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="normal">Normal</option>
                    <option value="csec">C-sec</option>
                </select>
                <InputError class="mt-2" :message="form.errors.actual_birth_type" />
            </div>

            <PrimaryButton :disabled="form.processing">Continue</PrimaryButton>
        </form>
    </CustomerAppLayout>
</template>
