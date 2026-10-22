<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    booking: { start_date: string | null };
    minStartDate: string;
    pendingConsent: boolean;
    balanceDue: number;
    canConfirm: boolean;
}>();

const form = useForm({
    start_date: '',
    actual_birth_type: 'normal',
});

const money = (sen: number) => (sen / 100).toFixed(2);
const submit = () => form.post(route('customer.start'));
const confirm = () => useForm({}).post(route('customer.start.confirm'));
</script>

<template>
    <CustomerLayout>
        <Head title="Start my package" />

        <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
            Start my package
        </h1>

        <div
            v-if="pendingConsent"
            class="rounded-md bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-200"
        >
            We're waiting for you to review a plan change — check your email for the link.
        </div>

        <div
            v-else-if="booking.start_date && balanceDue > 0"
            class="rounded-md bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-200"
        >
            A balance of RM {{ money(balanceDue) }} is still due before your package can start.
        </div>

        <div v-else-if="booking.start_date && canConfirm" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Start date: {{ booking.start_date }}. Everything is ready — confirm to begin
                deliveries.
            </p>
            <SecondaryButton @click="confirm">Confirm start</SecondaryButton>
        </div>

        <form v-else class="space-y-4" @submit.prevent="submit">
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="normal">Normal</option>
                    <option value="csec">C-sec</option>
                </select>
                <InputError class="mt-2" :message="form.errors.actual_birth_type" />
            </div>

            <PrimaryButton :disabled="form.processing">Continue</PrimaryButton>
        </form>
    </CustomerLayout>
</template>
