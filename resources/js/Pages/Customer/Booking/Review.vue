<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import AlertBanner from '@/Components/AlertBanner.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    draft: {
        edd: string;
        is_twins?: boolean;
        recipient_name?: string;
        address: { line1: string; line2?: string; postcode: string };
    };
    variant: {
        birth_type: string;
        duration_days: number;
        meal_plan: string;
        package: { name: Record<string, string> };
    };
    quote: { price_sen: number; daily_rate_sen: number };
}>();

const money = (sen: number) => (sen / 100).toFixed(2);

const form = useForm({});
const submit = () => form.post(route('customer.booking.submit'));

const errors = computed(() => form.errors as Record<string, string>);
const addressError = computed(() => errors.value['address.postcode']);
const otherErrors = computed(() =>
    Object.entries(errors.value).filter(([key]) => key !== 'address.postcode'),
);
</script>

<template>
    <CustomerAppLayout>
        <Head title="Review your booking" />

        <PageHeader title="Review your booking" />

        <div
            class="space-y-3 rounded-2xl border border-gray-200 bg-white shadow-sm p-5 text-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div v-if="draft.recipient_name" class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">For</span>
                <span class="text-gray-900 dark:text-gray-100">{{ draft.recipient_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Package</span>
                <span class="text-gray-900 dark:text-gray-100">{{ variant.package.name.en }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Plan</span>
                <span class="text-gray-900 dark:text-gray-100">
                    {{ variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                    · {{ variant.duration_days }} days ·
                    {{ variant.meal_plan === 'lunch_dinner' ? 'Lunch + Dinner' : 'Lunch only' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Due date</span>
                <span class="text-gray-900 dark:text-gray-100">{{ formatDate(draft.edd) }}</span>
            </div>
            <div v-if="draft.is_twins" class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Twins</span>
                <span class="text-gray-900 dark:text-gray-100">Yes</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Delivery</span>
                <span class="text-gray-900 dark:text-gray-100">
                    {{ draft.address.line1
                    }}<span v-if="draft.address.line2">, {{ draft.address.line2 }}</span
                    >,
                    {{ draft.address.postcode }}
                </span>
            </div>
            <div
                class="flex justify-between border-t border-gray-200 pt-3 font-medium dark:border-gray-800 dark:bg-gray-900"
            >
                <span class="text-gray-700 dark:text-gray-300">Total</span>
                <span class="text-gray-900 dark:text-gray-100"
                    >RM {{ money(quote.price_sen) }}</span
                >
            </div>
        </div>

        <AlertBanner v-if="addressError" tone="red" class="mt-4">
            <p>{{ addressError }}</p>
            <Link
                :href="route('customer.booking.details')"
                class="mt-1 inline-block font-medium underline"
            >
                Edit delivery address
            </Link>
        </AlertBanner>

        <AlertBanner v-if="otherErrors.length" tone="red" class="mt-4">
            <p v-for="[key, message] in otherErrors" :key="key">{{ message }}</p>
        </AlertBanner>

        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            No payment is taken yet. Your booking will be reviewed, and you'll get payment
            instructions once it's approved.
        </p>

        <form class="mt-6" @submit.prevent="submit">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Submit for approval
            </PrimaryButton>
        </form>
    </CustomerAppLayout>
</template>
